<?php

namespace App\DTOs;

use App\Models\Setting;

class SettingDTO {

    public function __construct(
        public string $name,
        public string $className,
        public string $orientation,
        public bool $toolBar,
        public bool $footer,
        public bool $footerFixed,
        public bool $isDarkMode,
        public string $updatedAt,
        public string $updatedBy,
    ) 
	{
	}

    public static function fromModel(Setting $setting, string $name): self
	{
        return new self(
            name: $setting->theme_name,
            className: $setting->theme_className,
            orientation: $setting->orientation,
            toolBar: $setting->toolbar,
            footer: $setting->footer,
            footerFixed: $setting->footer_fixed,
            isDarkMode: $setting->isDarkMode === 'dark' ? true : false,
            updatedAt: $setting->updated_at->toDateTimeString(),
            updatedBy: $name
        );
    }
}
