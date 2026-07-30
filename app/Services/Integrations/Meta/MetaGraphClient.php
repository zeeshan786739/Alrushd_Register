<?php

namespace App\Services\Integrations\Meta;

use Illuminate\Support\Facades\Http;

class MetaGraphClient
{
    private string $version;

    public function __construct()
    {
        $this->version = (string) config('integrations.meta.graph_version', 'v21.0');
    }

    public function oauthDialogUrl(string $redirectUri, string $state): string
    {
        $scopes = implode(',', config('integrations.meta.oauth_scopes', []));

        return 'https://www.facebook.com/'.$this->version.'/dialog/oauth?'.http_build_query([
            'client_id' => config('integrations.meta.app_id'),
            'redirect_uri' => $redirectUri,
            'state' => $state,
            'scope' => $scopes,
        ]);
    }

    /** @return array{access_token: string, token_type?: string, expires_in?: int} */
    public function exchangeCodeForToken(string $code, string $redirectUri): array
    {
        return $this->get('/oauth/access_token', [
            'client_id' => config('integrations.meta.app_id'),
            'client_secret' => config('integrations.meta.app_secret'),
            'redirect_uri' => $redirectUri,
            'code' => $code,
        ]);
    }

    /** @return array{access_token: string, expires_in?: int} */
    public function exchangeForLongLivedUserToken(string $shortLivedToken): array
    {
        return $this->get('/oauth/access_token', [
            'grant_type' => 'fb_exchange_token',
            'client_id' => config('integrations.meta.app_id'),
            'client_secret' => config('integrations.meta.app_secret'),
            'fb_exchange_token' => $shortLivedToken,
        ]);
    }

    /** @return array<int, array{id: string, name: string, access_token: string}> */
    public function listManagedPages(string $userAccessToken): array
    {
        $response = $this->get('/me/accounts', [
            'access_token' => $userAccessToken,
            'fields' => 'id,name,access_token',
        ]);

        return $response['data'] ?? [];
    }

    /** @return array<int, array<string, mixed>> */
    public function listLeadForms(string $pageId, string $pageAccessToken): array
    {
        $response = $this->get('/'.$pageId.'/leadgen_forms', [
            'access_token' => $pageAccessToken,
            'fields' => 'id,name,status,created_time',
        ]);

        return $response['data'] ?? [];
    }

    /** @return array<string, mixed> */
    public function fetchLead(string $leadgenId, string $pageAccessToken): array
    {
        return $this->get('/'.$leadgenId, [
            'access_token' => $pageAccessToken,
            'fields' => 'id,created_time,field_data,ad_id,form_id,campaign_id',
        ]);
    }

    /** @return array<string, mixed> */
    public function fetchLeadForm(string $formId, string $pageAccessToken): array
    {
        return $this->get('/'.$formId, [
            'access_token' => $pageAccessToken,
            'fields' => 'id,name,status',
        ]);
    }

    public function subscribePageToLeadgen(string $pageId, string $pageAccessToken): bool
    {
        $response = Http::asForm()->post($this->url('/'.$pageId.'/subscribed_apps'), [
            'subscribed_fields' => 'leadgen',
            'access_token' => $pageAccessToken,
        ]);

        $response->throw();

        return (bool) ($response->json('success') ?? false);
    }

    public function unsubscribePageFromLeadgen(string $pageId, string $pageAccessToken): bool
    {
        $response = Http::delete($this->url('/'.$pageId.'/subscribed_apps'), [
            'access_token' => $pageAccessToken,
        ]);

        if ($response->failed()) {
            return false;
        }

        return (bool) ($response->json('success') ?? true);
    }

    /** @param  array<string, mixed>  $query */
    private function get(string $path, array $query = []): array
    {
        $response = Http::get($this->url($path), $query);

        if ($response->failed()) {
            throw MetaGraphException::fromResponse($response);
        }

        return $response->json() ?? [];
    }

    private function url(string $path): string
    {
        $path = ltrim($path, '/');

        return 'https://graph.facebook.com/'.$this->version.'/'.$path;
    }
}
