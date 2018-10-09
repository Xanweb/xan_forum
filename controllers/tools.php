<?php
namespace Concrete\Package\XanForum\Controller;

use Concrete\Core\Page\Type\Type as CollectionType;
use XanForum\PageTemplateAreas;
use Concrete\Core\Entity\File\Version as FileVersion;
use FileImporter;
use Controller;
use FileSet;
use User;
use Core;

class Tools extends Controller
{
    protected $controllerActionPath = "/ccm/xan_forum/tools";

    public static function getPageTemplates($ctID)
    {
        $ct = CollectionType::getByID($ctID);
        $ret = ['' => t('Please select')];

        if (!is_object($ct)) {
            return $ret;
        }

        foreach ($ct->getPageTypePageTemplateObjects() as $pTemplate) {
            $ret[$pTemplate->getPageTemplateHandle()] = $pTemplate->getPageTemplateDisplayName();
        }

        Core::make('helper/ajax')->sendResult($ret);
    }

    /**
     * @param int $pTemplateHandle Page Template Handle
     */
    public function getAreas($pTemplateHandle)
    {
        $areas = PageTemplateAreas::getAreas($pTemplateHandle);
        $this->app->make('helper/ajax')->sendResult($areas);
    }

    public function uploadFile()
    {
        $token = $this->app->make('token');
        $ajax = $this->app->make('helper/ajax');
        $config = $this->app->make('config');
        $helperFile = $this->app->make('helper/concrete/file');

        $file = new \stdClass();
        $file->timestamp = $this->request->post('timestamp');

        $error = [];
        if (!$token->validate('upload_file')) {  // check token
            $error[] = t('Bad token');
        }

        if ($_FILES["file"]["error"] > 0) {
            $error[] = $_FILES["file"]["error"];
        }

        if (!empty($error)) {
            $errorStr = implode(', ', $error);
            $file->error = $errorStr . '.';
            $ajax->sendResult($file);
        }

        if (id(new User())->isRegistered()) {
            $maxFileSize = $config->get('conversations.files.registered.max_size');
            $maxQuantity = $config->get('conversations.files.registered.max');
        } else {
            $maxFileSize = $config->get('conversations.files.guest.max_size');
            $maxQuantity = $config->get('conversations.files.guest.max');
        }

        if ($maxFileSize > 0 && filesize($_FILES["file"]["tmp_name"]) > $maxFileSize * 1000000) {  // max upload size
            $error[] = t('File size exceeds limit');
        }

        // check file count (this is just for presentation, final count check is done on message submit).
        if ($maxQuantity > 0 && ($this->request->post('fileCount')) > $maxQuantity) {
            $error[] = t('Attachment limit reached');
        }

        // check filetype extension and overrides
        $extensionList = $helperFile->unserializeUploadFileExtensions($config->get('conversations.files.allowed_types'));
        $incomingExtension = end(explode('.', $_FILES["file"]["name"]));
        if ($incomingExtension && count($extensionList)) {  // check against block file extensions override
            foreach ($extensionList as $extension) {
                if (strtolower($extension) == strtolower($incomingExtension)) {
                    $validExtension = true;
                    break;
                }
            }
            if (!$validExtension) {
                $error[] = t('Invalid File Extension');
            }
        }

        if (!empty($error)) {
            $errorStr = implode(', ', $error);
            $file->error = $errorStr . '.';
            $ajax->sendResult($file);
        }
        // -- end intitial validation -- //

        // begin file import
        $fi = new FileImporter();
        $fv = $fi->import($_FILES["file"]["tmp_name"], $_FILES["file"]["name"]);
        if (!($fv instanceof FileVersion)) {
            $file->error = $fi->getErrorMessage($fv);
        } else {
            $file_set = $config->get('conversations.attachments_pending_file_set');
            $fs = FileSet::getByName($file_set);
            if (!is_object($fs)) {
                $fs = FileSet::createAndGetSet($file_set, FileSet::TYPE_PUBLIC, USER_SUPER_ID);
            }
            $fs->addFileToSet($fv);
            $file->id = $fv->getFileID();
            $file->tag = $this->request->post('tag');
        }

        $ajax->sendResult($file);
    }
}
