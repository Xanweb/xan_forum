<?php
namespace XanForum;

use Concrete\Core\Conversation\Editor\Editor as ConversationEditor;
use Concrete\Core\Support\Facade\Facade;

class App
{
    private static $pkg;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private static $em;

    /**
     * Get current package handle.
     *
     * @return string
     */
    public static function pkgHandle()
    {
        return 'xan_forum';
    }

    /**
     * Get current package object.
     *
     * @return \Package
     */
    public static function pkg()
    {
        if (!is_object(self::$pkg)) {
            self::$pkg = Facade::getFacadeApplication()
                ->make('Concrete\Core\Package\PackageService')
                ->getByHandle(self::pkgHandle());
        }

        return self::$pkg;
    }

    /**
     * Gets a package specific entity manager.
     *
     * @param bool $reset
     *
     * @return \Doctrine\ORM\EntityManager
     */
    public static function em($reset = false)
    {
        if (!self::$em || $reset) {
            self::$em = Facade::getFacadeApplication()->make('database/orm')->entityManager();
        }

        return self::$em;
    }

    /**
     * Get Xanweb Config.
     */
    public static function cfg($name)
    {
        return Facade::getFacadeApplication()->make('config')->get('xanweb.' . $name);
    }

    /**
     * Get Package Database Config.
     *
     * @return \Concrete\Core\Config\Repository\Liaison
     */
    public static function config()
    {
        return self::pkg()->getConfig();
    }

    /**
     * @return \Concrete\Core\Config\Repository\Liaison
     */
    public static function getFileConfig()
    {
        return self::pkg()->getFileConfig();
    }

    public static function setupAlias()
    {
        $aliasList = \Concrete\Core\Foundation\ClassAliasList::getInstance();
        $aliasList->register(static::getPackageAlias(), get_class());
    }

    protected static function getPackageAlias()
    {
        return camelcase(static::pkgHandle());
    }

    /**
     * @return ConversationEditor|null
     */
    public static function getForumEditor()
    {
        $cnvEditorHandle = static::cfg('forum.editor');
        if (!empty($cnvEditorHandle)) {
            $editor = ConversationEditor::getByHandle($cnvEditorHandle);
        }
        if (!is_object($editor)) {
            $editor = ConversationEditor::getActive();
        }

        return $editor;
    }
}
