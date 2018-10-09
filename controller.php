<?php
namespace Concrete\Package\XanForum;

use Concrete\Core\Page\Page;
use Concrete\Core\User\Group\Group;
use Concrete\Package\XanForum\Controller\Conversations\Message as ConversationMessage;
use Concrete\Package\XanForum\Controller\Conversations\Conversation;
use Concrete\Package\XanForum\Controller\Frontend\AssetsLocalization;
use Concrete\Package\XanForum\Controller\Import\ImportUsers;
use Concrete\Package\XanForum\Controller\Tools;
use Concrete\Core\Foundation\Service\ProviderList;
use Concrete\Core\Conversation\Editor\Editor as ConversationEditor;
use Concrete\Core\Asset\AssetList;
use Concrete\Core\Package\Package;
use XanForum\Utility\Installer;
use XanForum\ServiceProvider;
use XanForum\App;

class Controller extends Package
{
    protected $pkgHandle = 'xan_forum';
    protected $appVersionRequired = '8.4.2';
    protected $pkgVersion = '1.0';
    protected $packageDependencies = ['ck_editor_plugins' => true];
    protected $pkgAutoloaderRegistries = [
        'src' => 'XanForum',
    ];

    public function getPackageName()
    {
        return t('Forum');
    }

    public function getPackageDescription()
    {
        return t('Installs the discussion board add-on');
    }

    public function install()
    {
        $this->registerServiceProvider();

        $pkg = parent::install();

        try {
            /* @var Installer $install */
            $install = $this->app->make('forum/installer');
            $install->installBlockTypes(['xan_conversation', 'xan_forum_topic']);
            $install->installPageAttributeKeys([
                'boolean' => [
                    [
                        'akHandle' => 'hide_forum_sidebar',
                        'akName' => 'Hide Forum Sidebar',
                        'akIsSearchable' => false,
                        'akCheckedByDefault' => '1',
                    ],
                ],
            ]);

            ConversationEditor::add('ck', 'CKEditor', $pkg);

            $removeAccountPage = $install->installSinglePage("/account/remove_account");
            $removeAccountPage->assignPermissions(Group::getByID(GUEST_GROUP_ID), ['view_page']);
        } catch (\Exception $e) {
            $pkg->uninstall();
            throw $e;
        }
    }

    public function uninstall()
    {
        parent::uninstall();
        $editor = ConversationEditor::getByHandle('ck');
        if (is_object($editor)) {
            $editor->delete();
        }
    }

    public function on_start()
    {
        App::setupAlias();
        $this->setupComposerAutoloader();
        $this->registerServiceProvider();
        $this->registerAssets();
        $this->registerRoutes();
    }

    private function setupComposerAutoloader()
    {
        if (file_exists(__DIR__ . '/vendor/autoload.php')) {
            require_once __DIR__ . '/vendor/autoload.php';
        }

        if (file_exists(DIR_BASE . '/vendor/autoload.php')) {
            require_once DIR_BASE . '/vendor/autoload.php';
        }
    }

    private function registerServiceProvider()
    {
        /** @var ProviderList $list */
        $list = $this->app->make(ProviderList::class);
        $list->registerProvider(ServiceProvider::class);
    }

    private function registerAssets()
    {
        $al = AssetList::getInstance();
        $al->registerMultiple([
            'xan/file-upload' => [
                ['javascript', 'js/file-upload.js', [], $this],
            ],
            'xan/conversation' => [
                ['javascript', 'js/conversations.js', [], $this],
                ['javascript-localized', '/ccm/xan_forum/assets/localization/conversations/js', [], $this],
            ],
        ]);

        $al->registerGroupMultiple([
            'xan/file-upload' => [
                [
                    ['javascript', 'jquery'],
                    ['javascript', 'jquery/ui'],
                    ['javascript-localized', 'jquery/ui'],
                    ['javascript', 'dropzone'],
                    ['javascript-localized', 'dropzone'],
                    ['javascript-localized', 'jquery/ui'],
                    ['javascript-localized', 'core/localization'],
                    ['javascript', 'xan/file-upload'],
                    ['css', 'font-awesome'],
                    ['css', 'jquery/ui'],
                ],
            ],
            'xan/conversation' => [
                [
                    ['javascript', 'jquery'],
                    ['javascript', 'jquery/ui'],
                    ['javascript-localized', 'jquery/ui'],
                    ['javascript', 'underscore'],
                    ['javascript', 'backbone'],
                    ['javascript', 'core/lightbox'],
                    ['javascript', 'dropzone'],
                    ['javascript-localized', 'dropzone'],
                    ['javascript', 'bootstrap/dropdown'],
                    ['javascript', 'bootstrap/tooltip'],
                    ['javascript', 'bootstrap/popover'],
                    ['javascript', 'core/events'],
                    ['javascript-localized', 'jquery/ui'],
                    ['javascript-localized', 'core/localization'],
                    ['javascript', 'core/app'],
                    ['javascript', 'xan/conversation'],
                    ['javascript-localized', 'xan/conversation'],
                    ['css', 'core/app'],
                    ['css', 'core/conversation'],
                    ['css', 'core/frontend/errors'],
                    ['css', 'font-awesome'],
                    ['css', 'bootstrap/dropdown'],
                    ['css', 'core/lightbox'],
                    ['css', 'jquery/ui'],
                ],
            ],
        ]);
    }

    private function registerRoutes()
    {
        /* @var \Concrete\Core\Routing\RouterInterface $router */
        $router = $this->app->make('Concrete\Core\Routing\RouterInterface');
        $router->registerMultiple([
            '/ccm/xan_forum/tools/get/templates/{ctID}' => [Tools::class . '::getPageTemplates'],
            '/ccm/xan_forum/tools/get/areas/{pTemplateHandle}' => [Tools::class . '::getAreas'],
            '/ccm/xan_forum/tools/file/upload' => [Tools::class . '::uploadFile'],
            '/ccm/xan_forum/assets/localization/conversations/js' => [AssetsLocalization::class . '::getConversationsJavascript'],
            '/ccm/xan_forum/conversation/view' => [Conversation::class . '::view'],
            '/ccm/xan_forum/conversation/page' => [Conversation::class . '::page'],
            '/ccm/xan_forum/conversation/file/add' => [Conversation::class . '::addFile'],
            '/ccm/xan_forum/conversation/message/add' => [ConversationMessage::class . '::add'],
            '/ccm/xan_forum/conversation/message/edit' => [ConversationMessage::class . '::edit'],
            '/ccm/xan_forum/conversation/message/update' => [ConversationMessage::class . '::update'],
            '/ccm/xan_forum/conversation/message/delete' => [ConversationMessage::class . '::delete'],
            '/ccm/xan_forum/conversation/message/detail' => [ConversationMessage::class . '::detail'],
            '/ccm/xan_forum/conversation/message/flag' => [ConversationMessage::class . '::flag'],
            '/ccm/xan_forum/conversation/message/rate' => [ConversationMessage::class . '::rate'],
            '/ccm/xan_forum/conversation/message/report' => [ConversationMessage::class . '::report'],
            '/ccm/xan_forum/conversation/message/delete-file' => [ConversationMessage::class . '::deleteFile'],
            '/ccm/import-users' => [ImportUsers::class . '::run'],
        ]);
    }
}
