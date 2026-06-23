<?php

namespace Config;

use CodeIgniter\Config\BaseService;

class Services extends BaseService
{
    public static function webApiClient(bool $getShared = true): \App\Libraries\WebApiClient
    {
        if ($getShared) {
            return static::getSharedInstance('webApiClient');
        }

        $config = config('App');

        return new \App\Libraries\WebApiClient(
            $config->webApiBaseUrl,
            $config->webApiKey
        );
    }

    public static function siteSettingsService(bool $getShared = true): \App\Services\SiteSettingsService
    {
        if ($getShared) {
            return static::getSharedInstance('siteSettingsService');
        }

        return new \App\Services\SiteSettingsService(static::webApiClient());
    }

    public static function siteMenuService(bool $getShared = true): \App\Services\SiteMenuService
    {
        if ($getShared) {
            return static::getSharedInstance('siteMenuService');
        }

        return new \App\Services\SiteMenuService(static::webApiClient());
    }

    public static function sitePageService(bool $getShared = true): \App\Services\SitePageService
    {
        if ($getShared) {
            return static::getSharedInstance('sitePageService');
        }

        return new \App\Services\SitePageService(static::webApiClient());
    }

    public static function siteCollectionService(bool $getShared = true): \App\Services\SiteCollectionService
    {
        if ($getShared) {
            return static::getSharedInstance('siteCollectionService');
        }

        return new \App\Services\SiteCollectionService(static::webApiClient());
    }

    public static function siteEntryService(bool $getShared = true): \App\Services\SiteEntryService
    {
        if ($getShared) {
            return static::getSharedInstance('siteEntryService');
        }

        return new \App\Services\SiteEntryService(static::webApiClient());
    }

    public static function siteCategoryService(bool $getShared = true): \App\Services\SiteCategoryService
    {
        if ($getShared) {
            return static::getSharedInstance('siteCategoryService');
        }

        return new \App\Services\SiteCategoryService(static::webApiClient());
    }

    public static function siteRedirectService(bool $getShared = true): \App\Services\SiteRedirectService
    {
        if ($getShared) {
            return static::getSharedInstance('siteRedirectService');
        }

        return new \App\Services\SiteRedirectService(static::webApiClient());
    }

    public static function blockRenderer(bool $getShared = true): \App\Libraries\BlockRenderer
    {
        if ($getShared) {
            return static::getSharedInstance('blockRenderer');
        }

        return new \App\Libraries\BlockRenderer();
    }

    public static function cacheInvalidator(bool $getShared = true): \App\Libraries\CacheInvalidator
    {
        if ($getShared) {
            return static::getSharedInstance('cacheInvalidator');
        }

        return new \App\Libraries\CacheInvalidator();
    }
}
