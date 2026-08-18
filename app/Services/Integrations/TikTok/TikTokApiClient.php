<?php

namespace App\Services\Integrations\TikTok;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class TikTokApiClient
{
    /**
     * Build the TikTok API for Business advertiser authorization URL.
     *
     * Official Marketing API docs instruct developers to use the
     * "Advertiser authorization URL" from My Apps rather than publishing a
     * constructor. This SaaS flow reconstructs that URL from config
     * (app_id, redirect_uri, state). Override integrations.tiktok.auth_url
     * if the URL generated in My Apps uses a different host.
     */
    public function authorizationUrl(string $state, string $redirectUri): string
    {
        $authUrl = rtrim((string) config('integrations.tiktok.auth_url'), '?&');

        return $authUrl.'?'.http_build_query([
            'app_id' => (string) config('integrations.tiktok.app_id'),
            'redirect_uri' => $redirectUri,
            'state' => $state,
        ]);
    }

    /**
     * Exchange a one-time authorization code for a Marketing API access token.
     *
     * VERIFIED: POST /open_api/v1.3/oauth2/access_token/
     * JSON body: app_id, secret, auth_code
     * Success: code === 0, data.access_token, optional data.advertiser_ids[]
     *
     * @return array{access_token: string, advertiser_ids: array<int, string>}
     */
    public function exchangeAuthCodeForToken(string $authCode): array
    {
        $response = $this->send(fn () => $this->http()
            ->acceptJson()
            ->asJson()
            ->post($this->endpoint('oauth2/access_token/'), [
                'app_id' => (string) config('integrations.tiktok.app_id'),
                'secret' => (string) config('integrations.tiktok.app_secret'),
                'auth_code' => $authCode,
            ]));

        $data = $this->successfulData($response);

        $accessToken = $data['access_token'] ?? null;
        if (! is_string($accessToken) || $accessToken === '') {
            throw TikTokApiException::missingAccessToken();
        }

        return [
            'access_token' => $accessToken,
            'advertiser_ids' => $this->stringIds($data['advertiser_ids'] ?? []),
        ];
    }

    /**
     * Retrieve advertiser accounts that granted this app permission.
     *
     * VERIFIED: GET /open_api/v1.3/oauth2/advertiser/get/
     * Header: Access-Token
     * Query: app_id, secret
     * Success: code === 0, data.list[].advertiser_id, data.list[].advertiser_name
     *
     * @return array<int, array{id: string, name: string}>
     */
    public function listAuthorizedAdvertisers(string $accessToken): array
    {
        $response = $this->send(fn () => $this->http()
            ->acceptJson()
            ->withHeaders([
                'Access-Token' => $accessToken,
            ])
            ->get($this->endpoint('oauth2/advertiser/get/'), [
                'app_id' => (string) config('integrations.tiktok.app_id'),
                'secret' => (string) config('integrations.tiktok.app_secret'),
            ]));

        $data = $this->successfulData($response);
        $list = $data['list'] ?? null;

        if (! is_array($list)) {
            throw TikTokApiException::invalidResponse();
        }

        $advertisers = [];

        foreach ($list as $row) {
            if (! is_array($row)) {
                continue;
            }

            $id = isset($row['advertiser_id']) ? trim((string) $row['advertiser_id']) : '';
            if ($id === '') {
                continue;
            }

            $name = isset($row['advertiser_name']) ? trim((string) $row['advertiser_name']) : '';

            $advertisers[] = [
                'id' => $id,
                'name' => $name !== '' ? $name : $id,
            ];
        }

        return $this->uniqueAdvertisers($advertisers);
    }

    /**
     * List Lead Generation Instant Forms for an advertiser.
     *
     * VERIFIED: GET /open_api/v1.3/page/get/
     * Header: Access-Token
     * Query: advertiser_id, business_type=LEAD_GEN, page, page_size
     * Success: code === 0, data.list[].page_id, title, status; data.page_info
     *
     * @return array<int, array{id: string, name: string, status: string}>
     */
    public function listInstantForms(string $accessToken, string $advertiserId): array
    {
        $forms = [];
        $page = 1;
        $pageSize = 100;
        $maxPages = 50;

        do {
            $response = $this->send(fn () => $this->http()
                ->acceptJson()
                ->withHeaders([
                    'Access-Token' => $accessToken,
                ])
                ->get($this->endpoint('page/get/'), [
                    'advertiser_id' => $advertiserId,
                    'business_type' => 'LEAD_GEN',
                    'page' => $page,
                    'page_size' => $pageSize,
                ]));

            $data = $this->successfulData($response);
            $list = $data['list'] ?? null;
            if (! is_array($list)) {
                throw TikTokApiException::invalidResponse();
            }

            foreach ($list as $row) {
                if (! is_array($row)) {
                    continue;
                }

                $id = isset($row['page_id']) ? trim((string) $row['page_id']) : '';
                if ($id === '') {
                    continue;
                }

                $name = isset($row['title']) ? trim((string) $row['title']) : '';
                $status = isset($row['status']) ? trim((string) $row['status']) : '';

                $forms[$id] = [
                    'id' => $id,
                    'name' => $name !== '' ? $name : $id,
                    'status' => $status,
                ];
            }

            $totalPages = (int) data_get($data, 'page_info.total_page', $page);
            $page++;
        } while ($page <= $totalPages && $page <= $maxPages);

        return array_values($forms);
    }

    /**
     * Retrieve Instant Form field/question names.
     *
     * VERIFIED: GET /open_api/v1.3/lead/field/get/
     * Header: Access-Token
     * Query: advertiser_id, lead_source=INSTANT_FORM, page_id
     * Success: code === 0, data.fields string[]
     *
     * @return array<int, array{id: string, label: string}>
     */
    public function getInstantFormFields(string $accessToken, string $advertiserId, string $pageId): array
    {
        $response = $this->send(fn () => $this->http()
            ->acceptJson()
            ->withHeaders([
                'Access-Token' => $accessToken,
            ])
            ->get($this->endpoint('lead/field/get/'), [
                'advertiser_id' => $advertiserId,
                'lead_source' => 'INSTANT_FORM',
                'page_id' => $pageId,
            ]));

        $data = $this->successfulData($response);
        $fields = $data['fields'] ?? [];

        if (is_string($fields)) {
            $decoded = json_decode($fields, true);
            $fields = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($fields)) {
            throw TikTokApiException::invalidResponse();
        }

        $normalized = [];

        foreach ($fields as $field) {
            if (! is_string($field) && ! is_numeric($field)) {
                continue;
            }

            $id = trim((string) $field);
            if ($id === '') {
                continue;
            }

            $normalized[$id] = [
                'id' => $id,
                'label' => $this->humanizeFieldLabel($id),
            ];
        }

        return array_values($normalized);
    }

    /**
     * Subscribe this advertiser to Instant Form lead webhooks.
     *
     * VERIFIED: POST /open_api/v1.3/subscription/subscribe/
     * JSON: app_id, secret, subscribe_entity=LEAD, callback_url,
     *       subscription_detail.access_token, subscription_detail.advertiser_id
     *
     * @return array{subscription_id: string}
     */
    public function subscribeLeadWebhooks(string $accessToken, string $advertiserId, string $callbackUrl): array
    {
        $response = $this->send(fn () => $this->http()
            ->acceptJson()
            ->asJson()
            ->post($this->endpoint('subscription/subscribe/'), [
                'app_id' => (string) config('integrations.tiktok.app_id'),
                'secret' => (string) config('integrations.tiktok.app_secret'),
                'subscribe_entity' => 'LEAD',
                'callback_url' => $callbackUrl,
                'subscription_detail' => [
                    'access_token' => $accessToken,
                    'advertiser_id' => $advertiserId,
                    'lead_source' => 'INSTANT_FORM',
                ],
            ]));

        $data = $this->successfulData($response);
        $subscriptionId = isset($data['subscription_id']) ? trim((string) $data['subscription_id']) : '';
        if ($subscriptionId === '') {
            throw TikTokApiException::invalidResponse();
        }

        return ['subscription_id' => $subscriptionId];
    }

    private function humanizeFieldLabel(string $field): string
    {
        if (! str_contains($field, '_') && ! str_contains($field, '-') && ! preg_match('/^[a-z0-9]+$/i', $field)) {
            return $field;
        }

        $label = trim(str_replace(['_', '-'], ' ', $field));

        return $label !== '' ? ucwords($label) : $field;
    }

    private function http(): \Illuminate\Http\Client\PendingRequest
    {
        $timeout = max(5, (int) config('integrations.tiktok.timeout', 20));

        return Http::timeout($timeout);
    }

    /**
     * Execute a TikTok HTTP call without leaking request URLs (which may contain app secret).
     */
    private function send(callable $request): Response
    {
        try {
            return $request();
        } catch (ConnectionException|RequestException) {
            throw new TikTokApiException('TikTok API request failed.');
        }
    }

    private function endpoint(string $path): string
    {
        return rtrim((string) config('integrations.tiktok.api_base'), '/').'/'.ltrim($path, '/');
    }

    /**
     * @return array<string, mixed>
     */
    private function successfulData(Response $response): array
    {
        if ($response->failed()) {
            throw TikTokApiException::fromHttpFailure($response);
        }

        $payload = $response->json();
        if (! is_array($payload) || ! array_key_exists('code', $payload)) {
            throw TikTokApiException::invalidResponse();
        }

        if ((int) $payload['code'] !== 0) {
            throw TikTokApiException::fromApiPayload($payload);
        }

        $data = $payload['data'] ?? null;
        if (! is_array($data)) {
            throw TikTokApiException::invalidResponse();
        }

        return $data;
    }

    /**
     * @param  mixed  $ids
     * @return array<int, string>
     */
    private function stringIds(mixed $ids): array
    {
        if (! is_array($ids)) {
            return [];
        }

        $normalized = [];

        foreach ($ids as $id) {
            if (is_scalar($id) && (string) $id !== '') {
                $normalized[] = (string) $id;
            }
        }

        return array_values(array_unique($normalized));
    }

    /**
     * @param  array<int, array{id: string, name: string}>  $advertisers
     * @return array<int, array{id: string, name: string}>
     */
    private function uniqueAdvertisers(array $advertisers): array
    {
        $unique = [];

        foreach ($advertisers as $advertiser) {
            $unique[$advertiser['id']] = $advertiser;
        }

        return array_values($unique);
    }
}
