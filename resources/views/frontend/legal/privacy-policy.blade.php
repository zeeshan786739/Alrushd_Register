@extends('layouts.landing')

@section('title', 'Privacy Policy — AL-Rushd')

@section('css')
<style>
    .legal-page { background: #F9F7F0; min-height: 100vh; padding: 48px 0 80px; }
    .legal-page__inner { max-width: 820px; margin: 0 auto; padding: 0 24px; }
    .legal-page__back { display: inline-flex; align-items: center; gap: 8px; color: #0a1c35; text-decoration: none; font-size: 14px; margin-bottom: 24px; }
    .legal-page__back:hover { color: #AE9A66; }
    .legal-card { background: #fff; border-radius: 16px; padding: 40px 48px; box-shadow: 0 4px 24px rgba(10,28,53,0.06); }
    .legal-card h1 { font-size: 32px; color: #0a1c35; margin: 0 0 8px; }
    .legal-card .legal-updated { color: #666; font-size: 14px; margin-bottom: 32px; }
    .legal-card h2 { font-size: 20px; color: #0a1c35; margin: 28px 0 12px; }
    .legal-card p, .legal-card li { color: #333; line-height: 1.7; font-size: 15px; }
    .legal-card ul { padding-left: 20px; margin: 0 0 16px; }
    .legal-card a { color: #AE9A66; }
    @media (max-width: 640px) { .legal-card { padding: 28px 20px; } }
</style>
@endsection

@section('content')
@php
    $b = $cms['branding'] ?? [];
    $contact = $landing['contact'] ?? [];
@endphp

<section class="legal-page">
    <div class="legal-page__inner">
        <a href="{{ url('/') }}" class="legal-page__back"><i class="fa fa-arrow-left"></i> Back to home</a>

        <article class="legal-card">
            <h1>Privacy Policy</h1>
            <p class="legal-updated">Last updated: {{ now()->format('F j, Y') }}</p>

            <p>
                This Privacy Policy explains how <strong>{{ $b['company_name'] ?? 'AL-Rushd Online School' }}</strong>
                (“we”, “us”, “our”) collects, uses, and protects personal information when you use our website,
                admission forms, and school administration portal at
                <a href="{{ config('app.url') }}">{{ parse_url(config('app.url'), PHP_URL_HOST) }}</a>
                (the “Services”).
            </p>

            <h2>1. Who we are</h2>
            <p>
                AL-Rushd provides online Islamic and modern education, admissions, and related administrative services.
                Our administrative portal is used by authorised school staff to manage enquiries, leads, admissions,
                and communications.
            </p>
            <ul>
                <li><strong>Email:</strong> <a href="mailto:{{ $contact['email'] ?? 'info@alrushd.online' }}">{{ $contact['email'] ?? 'info@alrushd.online' }}</a></li>
                <li><strong>Phone:</strong> {{ $contact['phone'] ?? '+44 20 3633 0757' }}</li>
                <li><strong>Address:</strong> {{ $contact['address'] ?? 'United Kingdom' }}</li>
            </ul>

            <h2>2. Information we collect</h2>
            <p>We may collect the following types of personal information:</p>
            <ul>
                <li><strong>Contact details</strong> — name, email address, phone number, address</li>
                <li><strong>Admissions information</strong> — student details, parent/guardian details, educational history, and documents you submit through our forms</li>
                <li><strong>Enquiry and referral data</strong> — information you provide when enquiring, booking a call, or submitting referral forms</li>
                <li><strong>Facebook &amp; Instagram Lead Ads</strong> — if you submit a lead form on Meta platforms, we receive the fields you provide (e.g. name, email, phone) via Meta’s Lead Ads service</li>
                <li><strong>Technical data</strong> — IP address, browser type, and usage data collected through cookies and similar technologies where applicable</li>
                <li><strong>Staff portal data</strong> — login and activity information for authorised administrators using our CRM and form management systems</li>
            </ul>

            <h2>3. How we use your information</h2>
            <p>We use personal information to:</p>
            <ul>
                <li>Process admissions, enquiries, and applications</li>
                <li>Respond to your requests and communicate about our programmes</li>
                <li>Import and manage leads from Facebook and Instagram Lead Ad forms in our CRM for admissions follow-up</li>
                <li>Provide and improve our Services</li>
                <li>Meet legal, regulatory, and safeguarding obligations</li>
                <li>Send service-related communications (you may opt out of marketing where applicable)</li>
            </ul>
            <p>We do <strong>not</strong> sell your personal information to third parties.</p>

            <h2>4. Facebook / Meta Lead Ads</h2>
            <p>
                When a school connects its Facebook Page to our CRM, lead submissions from Meta Lead Ad forms are
                transmitted to our servers using Meta’s webhooks and Graph API. We only receive data that the user
                submits on the lead form. This data is stored in our secure CRM for admissions purposes only.
            </p>
            <p>
                Meta’s own privacy practices are governed by
                <a href="https://www.facebook.com/privacy/policy/" target="_blank" rel="noopener">Meta’s Privacy Policy</a>.
                Each school is responsible for its own Facebook Page and ad campaigns; our platform provides the
                technical integration to import leads into the school’s CRM.
            </p>

            <h2>5. Legal basis (UK / GDPR)</h2>
            <p>Where UK GDPR applies, we process personal data on the basis of:</p>
            <ul>
                <li><strong>Consent</strong> — where you submit a form or opt in to communications</li>
                <li><strong>Contract</strong> — to process admissions and provide requested services</li>
                <li><strong>Legitimate interests</strong> — to manage enquiries, improve our Services, and ensure security, balanced against your rights</li>
                <li><strong>Legal obligation</strong> — where required by law</li>
            </ul>

            <h2>6. Sharing your information</h2>
            <p>We may share information with:</p>
            <ul>
                <li>Service providers who help us operate our website, hosting, email, and payment systems (under data processing agreements where required)</li>
                <li>Meta Platforms, Inc. — when you interact with our Lead Ad forms on Facebook or Instagram</li>
                <li>Authorities or regulators when required by law or to protect rights and safety</li>
            </ul>
            <p>We do not share personal data with third parties for their own marketing purposes.</p>

            <h2>7. Data retention</h2>
            <p>
                We retain personal information for as long as necessary to fulfil the purposes described in this policy,
                including admissions records, CRM history, and legal retention requirements. When data is no longer needed,
                we securely delete or anonymise it.
            </p>

            <h2>8. Security</h2>
            <p>
                We implement appropriate technical and organisational measures to protect personal information,
                including encrypted connections (HTTPS), access controls for staff accounts, and secure server hosting.
                No method of transmission over the internet is 100% secure; we cannot guarantee absolute security.
            </p>

            <h2>9. Your rights</h2>
            <p>Depending on your location, you may have the right to:</p>
            <ul>
                <li>Access the personal data we hold about you</li>
                <li>Request correction or deletion of your data</li>
                <li>Object to or restrict certain processing</li>
                <li>Withdraw consent where processing is based on consent</li>
                <li>Lodge a complaint with the UK Information Commissioner’s Office (ICO) at <a href="https://ico.org.uk" target="_blank" rel="noopener">ico.org.uk</a></li>
            </ul>
            <p>To exercise your rights, contact us at <a href="mailto:{{ $contact['email'] ?? 'info@alrushd.online' }}">{{ $contact['email'] ?? 'info@alrushd.online' }}</a>.</p>

            <h2>10. Cookies</h2>
            <p>
                Our website may use cookies and similar technologies to enable core functionality, analytics, and
                security. You can control cookies through your browser settings. Essential cookies may be required
                for forms and login sessions to work correctly.
            </p>

            <h2>11. Children</h2>
            <p>
                Our Services relate to school admissions. Personal information about children is typically provided
                by parents or legal guardians. We process such information only for legitimate educational and
                admissions purposes with appropriate safeguards.
            </p>

            <h2>12. Changes to this policy</h2>
            <p>
                We may update this Privacy Policy from time to time. The “Last updated” date at the top will reflect
                any changes. Continued use of our Services after changes constitutes acceptance of the updated policy.
            </p>

            <h2>13. Contact us</h2>
            <p>
                If you have questions about this Privacy Policy or how we handle your data, please contact:
            </p>
            <ul>
                <li><strong>Email:</strong> <a href="mailto:{{ $contact['email'] ?? 'info@alrushd.online' }}">{{ $contact['email'] ?? 'info@alrushd.online' }}</a></li>
                <li><strong>Website:</strong> <a href="{{ config('app.url') }}">{{ config('app.url') }}</a></li>
            </ul>
        </article>
    </div>
</section>
@endsection
