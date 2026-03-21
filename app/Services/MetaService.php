<?php

namespace App\Services;

use App\Contracts\UserType;
use App\Models\DbVersion;
use App\Models\Navigation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class MetaService 
{

	public function handleMetaData(): array
	{
		$latestVersion = $this->getDbVersions();

		if ($latestVersion) {
			$latestVersion->version = 'DB: ' . $latestVersion->version;
		}

		return [
			'dbVersions' => $latestVersion,
			'appVersion' => config('app.version'),
		];
	}

	public function handleGetNavigationData(): Collection 
	{
		return $this->getNavigation();
	}

	private function canAccess($item, $user): bool 
	{
		return in_array($user['type'], [
			UserType::Admin->value,
			UserType::Guest->value,
			$item['type'],
		], true);
	}

	private function getNavigation(): Collection 
	{
		return Navigation::orderBy('order')
			->get()
			->groupBy('header')
			->map(fn($items, $header) => [
				'header' => $header,
				'items' => $items,
			])
			->values();
	}

	private function getDbVersions(): DbVersion 
	{
		return DbVersion::select('version', 'created_at', 'updated_at')
			->latest()
			->first();
	}

}
