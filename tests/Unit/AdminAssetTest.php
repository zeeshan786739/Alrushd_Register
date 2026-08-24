<?php

namespace Tests\Unit;

use App\Support\AdminAsset;
use Tests\TestCase;

class AdminAssetTest extends TestCase
{
    public function test_version_uses_filemtime_for_existing_file(): void
    {
        $path = 'admin/assets/css/alrushad-overrides.css';
        $version = AdminAsset::version($path);
        $this->assertNotNull($version);
        $this->assertSame((string) filemtime(public_path($path)), $version);

        $url = AdminAsset::url($path);
        $this->assertStringContainsString('alrushad-overrides.css?v='.$version, $url);
    }

    public function test_missing_file_falls_back_without_query(): void
    {
        $path = 'admin/assets/css/does-not-exist-'.uniqid().'.css';
        $this->assertNull(AdminAsset::version($path));
        $this->assertSame(asset($path), AdminAsset::url($path));
        $this->assertStringNotContainsString('?v=', AdminAsset::url($path));
    }
}
