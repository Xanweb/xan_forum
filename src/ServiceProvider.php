<?php
namespace XanForum;

use Concrete\Core\Foundation\Service\Provider as CoreServiceProvider;
use XanForum\Service\UserStatus;
use XanForum\Utility\Installer;

class ServiceProvider extends CoreServiceProvider
{
    public function register()
    {
        // Register CkEditor for Forum
        $this->app->bind(['\XanForum\Conversation\Editor\CkEditor' => 'Concrete\Core\Conversation\Editor\CkEditor'], true);
        $this->app->bind('forum/installer', function ($app) {
            return $app->make(Installer::class, [App::pkg()]);
        });

        $this->app->singleton('xan/user/status', UserStatus::class);
    }
}
