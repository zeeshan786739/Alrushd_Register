-- ════════════════════════════════════════════════════════════════════════
--  Al-Rushd CRM — make a PRODUCTION CLONE safe to use as STAGING.
--  Run ONLY on the staging database, right after cloning and BEFORE starting
--  queue workers or cron. It removes credentials that would let staging email
--  parents, take card payments, read the school mailbox or call Meta/TikTok.
--
--    mysql -u <staging_db_user> -p <staging_database> < deploy/staging-sanitize.sql
--
--  Re-run it every time staging is refreshed from production.
-- ════════════════════════════════════════════════════════════════════════

-- Safety check: refuse to run on a database whose name doesn't contain "staging".
SET @db := DATABASE();
SELECT IF(@db LIKE '%staging%', 'OK: sanitising staging database', 'STOP: not a staging database') AS check_result;
SET @guard := IF(@db LIKE '%staging%', 1, NULL);
-- (Every statement below is no-op unless the guard is set.)

-- Queued and failed jobs copied from production (they would send real emails)
DELETE FROM jobs        WHERE @guard = 1;
DELETE FROM failed_jobs WHERE @guard = 1;
DELETE FROM job_batches WHERE @guard = 1;
DELETE FROM sessions    WHERE @guard = 1;

-- Email Marketing: mailbox + SMTP + SendGrid credentials
UPDATE em_mailbox_settings
   SET imap_host = NULL, imap_username = NULL, imap_password = NULL,
       smtp_host = NULL, smtp_username = NULL, smtp_password = NULL,
       sendgrid_api_key = NULL
 WHERE @guard = 1;
UPDATE em_sender_mailboxes
   SET imap_host = NULL, imap_username = NULL, imap_password = NULL
 WHERE @guard = 1;

-- School fee payments: no live Stripe keys on staging
UPDATE organization_payment_settings
   SET stripe_publishable_key = NULL, stripe_secret = NULL, stripe_webhook_secret = NULL,
       is_enabled = 0, test_mode = 1
 WHERE @guard = 1;

-- Facebook Lead Ads / TikTok connections
UPDATE integration_connections
   SET status = 'disconnected', access_token = NULL
 WHERE @guard = 1;

-- Global settings: payment, tracking-API and mail secrets
UPDATE settings
   SET stripe_key = NULL, stripe_secret = NULL,
       meta_access_token = NULL, meta_test_event_code = NULL,
       ga4_api_secret = NULL, gtm_server_token = NULL,
       tiktok_access_token = NULL, mail_password = NULL
 WHERE @guard = 1;

-- Website CMS: stop the staging site loading production analytics/pixels
UPDATE website_cms
   SET draft = JSON_SET(COALESCE(draft, JSON_OBJECT()),
                 '$.analytics', JSON_OBJECT('google_analytics', '', 'google_tag_manager', '', 'facebook_pixel', '', 'hotjar', '', 'microsoft_clarity', '')),
       published = JSON_SET(COALESCE(published, JSON_OBJECT()),
                 '$.analytics', JSON_OBJECT('google_analytics', '', 'google_tag_manager', '', 'facebook_pixel', '', 'hotjar', '', 'microsoft_clarity', ''))
 WHERE @guard = 1;

SELECT IF(@guard = 1,
          'Done. Next: php artisan migrate --force, then website:connect-headless (see docs/DEPLOYMENT.md).',
          'NOTHING CHANGED: the database name must contain "staging".') AS result;
