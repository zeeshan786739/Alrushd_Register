<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class FrontendConfigController extends Controller
{
    public function config(): JsonResponse
    {
        try {
            $settings = Setting::first();
        } catch (\Throwable) {
            $settings = null;
        }

        $heroButtons = $this->heroButtonsFromDatabase();

        return response()->json([
            'hero_buttons' => $heroButtons,
            'header_links' => $this->formLinksForPlacement('header'),
            'footer_links' => $this->formLinksForPlacement('footer'),
            'ctas' => $this->ctasFromForms(),
            'footer_explore' => config('frontend.footer_explore'),
            'form_endpoints' => $this->formEndpoints(),
            'site' => [
                'company_name' => $settings?->company_name ?? 'Al-Rushd Online School',
                'email' => $settings?->email_one ?? 'admin@alrushd.org.uk',
                'phone' => $settings?->phone_one ?? '+44 20 3633 0757',
                'address' => $settings?->address,
                'facebook' => $settings?->facebook,
                'instagram' => $settings?->instagram,
                'linkedin' => $settings?->linkedin,
                'copyright' => $settings?->copyright,
            ],
            'payment' => [
                'stripe_key' => $settings?->stripe_key,
                'online_enabled' => (bool) ($settings?->payment_method_status ?? 1),
            ],
        ]);
    }

    private function heroButtonsFromDatabase(): array
    {
        try {
            $forms = Form::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get()
                ->filter(fn (Form $form) => $form->hasPlacement('landing'));

            if ($forms->isNotEmpty()) {
                $buttons = $forms->map(fn (Form $form) => [
                    'label' => $form->hero_label ?: $form->name,
                    'href' => $form->routePath(),
                    'variant' => $form->hero_variant ?: 'outline',
                    'slug' => $form->slug,
                    'handler' => $form->handler,
                ])->values()->all();

                $buttons[] = [
                    'label' => 'Profile',
                    'href' => '/admin/login',
                    'variant' => 'outline',
                    'slug' => 'admin-login',
                    'handler' => 'custom',
                ];

                return $buttons;
            }
        } catch (\Throwable) {
            // Fall back to config when migrations haven't run yet.
        }

        return config('frontend.hero_buttons');
    }

    private function ctasFromForms(): array
    {
        $defaults = config('frontend.ctas');

        try {
            $forms = Form::query()->where('is_active', true)->get();
            foreach ($forms as $form) {
                $key = str_replace('-', '_', $form->slug);
                $defaults[$key] = $form->routePath();
            }
        } catch (\Throwable) {
            //
        }

        return $defaults;
    }

    private function formEndpoints(): array
    {
        $defaults = config('frontend.form_endpoints');

        try {
            $forms = Form::query()->where('is_active', true)->where('handler', 'dynamic')->get();
            foreach ($forms as $form) {
                $defaults[$form->slug] = '/api/frontend/forms/'.$form->slug.'/submit';
            }
        } catch (\Throwable) {
            //
        }

        return $defaults;
    }

    private function formLinksForPlacement(string $placement): array
    {
        try {
            return Form::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get()
                ->filter(fn (Form $form) => $form->hasPlacement($placement))
                ->map(fn (Form $form) => $form->toNavLink())
                ->values()
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }
}
