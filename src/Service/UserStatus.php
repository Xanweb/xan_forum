<?php
namespace XanForum\Service;

use XanForum\App;
use HtmlObject\Image;

class UserStatus
{
    const ONLINE_ICON = 'online-icon.png';
    const OFFLINE_ICON = 'offline-icon.png';

    /**
     * @param \Concrete\Core\Entity\User\User|int $uo user object or user id
     *
     * @return bool
     */
    public function isOnlineNow($uo)
    {
        $uLastOnline = 0;
        if (is_object($uo)) {
            $uLastOnline = $uo->getLastOnline();
        } elseif (is_numeric($uo)) {
            $db = \Database::connection();
            $uLastOnline = $db->fetchColumn("select uLastOnline from Users where uID = ?", [$uo]);
        }

        return (time() - $uLastOnline) <= ONLINE_NOW_TIMEOUT;
    }

    /**
     * @param \Concrete\Core\Entity\User\User|int $uo user object or user id
     *
     * @return Image
     */
    public function getStatusIcon($uo)
    {
        $packRelativePath = App::pkg()->getRelativePath();

        if ($this->isOnlineNow($uo)) {
            return Image::create(implode(DIRECTORY_SEPARATOR, [$packRelativePath, 'images', static::ONLINE_ICON]));
        }

        return Image::create(implode(DIRECTORY_SEPARATOR, [$packRelativePath, 'images', static::OFFLINE_ICON]));
    }
}
