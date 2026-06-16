<?php

namespace Config;

use CodeIgniter\Config\BaseService;

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends BaseService
{
    /**
     * Web API Client for communicating with ci4-website-builder-domain.
     */
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

    /**
     * Site Settings Service - fetches public settings from the API.
     */
    public static function siteSettingsService(bool $getShared = true): \App\Services\SiteSettingsService
    {
        if ($getShared) {
            return static::getSharedInstance('siteSettingsService');
        }

        return new \App\Services\SiteSettingsService(static::webApiClient());
    }

    /**
     * Site Menu Service - fetches menu trees from the API.
     */
    public static function siteMenuService(bool $getShared = true): \App\Services\SiteMenuService
    {
        if ($getShared) {
            return static::getSharedInstance('siteMenuService');
        }

        return new \App\Services\SiteMenuService(static::webApiClient());
    }

    /**
     * Site Page Service - fetches CMS pages from the API.
     */
    public static function sitePageService(bool $getShared = true): \App\Services\SitePageService
    {
        if ($getShared) {
            return static::getSharedInstance('sitePageService');
        }

        return new \App\Services\SitePageService(static::webApiClient());
    }

    /**
     * Site Collection Service - fetches collections from the API.
     */
    public static function siteCollectionService(bool $getShared = true): \App\Services\SiteCollectionService
    {
        if ($getShared) {
            return static::getSharedInstance('siteCollectionService');
        }

        return new \App\Services\SiteCollectionService(static::webApiClient());
    }

    /**
     * Site Entry Service - fetches collection entries from the API.
     */
    public static function siteEntryService(bool $getShared = true): \App\Services\SiteEntryService
    {
        if ($getShared) {
            return static::getSharedInstance('siteEntryService');
        }

        return new \App\Services\SiteEntryService(static::webApiClient());
    }

    /**
     * Site Redirect Service - resolves redirects from the API.
     */
    public static function siteRedirectService(bool $getShared = true): \App\Services\SiteRedirectService
    {
        if ($getShared) {
            return static::getSharedInstance('siteRedirectService');
        }

        return new \App\Services\SiteRedirectService(static::webApiClient());
    }

    /**
     * Block Renderer - renders content blocks dynamically by block_key.
     */
    public static function blockRenderer(bool $getShared = true): \App\Libraries\BlockRenderer
    {
        if ($getShared) {
            return static::getSharedInstance('blockRenderer');
        }

        return new \App\Libraries\BlockRenderer();
    }
}
