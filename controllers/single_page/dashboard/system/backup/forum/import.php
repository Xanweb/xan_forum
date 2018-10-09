<?php
namespace Concrete\Package\XanForum\Controller\SinglePage\Dashboard\System\Backup\Forum;

use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Package\XanForum\Controller\Import\ImportUsers;
use PageTemplate;
use Core;
use BlockType;
use Page;
use Concrete\Core\Page\Type\Type as CollectionType;
use Concrete\Package\XanForum\Controller\Conversations\Message as ConversationMessageController;
use Concrete\Core\File\EditResponse as FileEditResponse;
use FileImporter;
use Loader;
use FilePermissions;
use XanForum\Service\BBCodeParser;
use XanForum\Utility\bbcode;

class Import extends DashboardPageController
{
    private $area;
    private $pt;
    private $ct;
    private $xml;
    private $txt;
    private $uir;
    private $forumPage;
    private $importUsers;
    private $bbCodeParser;
    private $bbtohtml;

    /**
     * Import constructor.
     *
     * @param Page $c
     */
    public function __construct(Page $c)
    {
        parent::__construct($c);
    }

    public function getCollectionTypeIDs()
    {
        $collectionTypes = CollectionType::getList();
        $ret[0] = t('Please select...');

        foreach ($collectionTypes as $collectionType/* @var $collectionType CollectionType */) {
            $ret[$collectionType->getPageTypeID()] = $collectionType->getPageTypeName();
        }

        return $ret;
    }

    public function import_pages_xml()
    {
        // $this->bbCodeParser = new BBCodeParser();
        $this->bbtohtml = new bbcode();
        $this->bbCodeParser = new BBCodeParser();
        set_time_limit(0);
        $this->importUsers = $_REQUEST['importUsers'];
        if ($this->importUsers) {
            $importUsers = new ImportUsers();
            $importUsers->run();
        }
        $this->txt = $this->app->make('helper/text');
        $this->uir = Core::make('Concrete\Core\User\UserInfoRepository');
        $this->xml = simplexml_load_file($_FILES['xml']['tmp_name'], \SimpleXMLElement::class, LIBXML_NOCDATA | LIBXML_NOBLANKS);
        $this->forumPage = Page::getByID($_REQUEST['forumPage']);
        $this->ct = CollectionType::getByID($_REQUEST['ctID']);
        $this->pt = PageTemplate::getByHandle($_REQUEST['ptID']);
        $this->area = $_REQUEST['area'];
        if (!empty($this->xml->pages->page)) {
            foreach ($this->xml->pages->page as $page) {
                // create new page for post
                $this->createNewPost($page);
            }
        }
    }

    /**
     *  create new page with our block and attributes for post.
     *
     * @param $page
     */
    private function createNewPost($page)
    {
        $post = $page->area->blocks->block->post;
        $authorName = (string) trim($post->cnvMessageAuthorName);
        //user is not deleted
        if ($authorID = $this->uir->getByName($authorName)->getUserObject()->getUserID()) {
            $authorEmail = (string) trim($post->cnvMessageAuthorEmail);
            $cnvMessageBody = $this->bbtohtml->tohtml((string) $post->cnvMessageBody);
            $cnvMessageBody = $this->bbCodeParser->parse($cnvMessageBody);
            $cnvMessageDateCreated = (string) trim($post->cnvMessageDateCreated);
            $data = [];
            $data['cName'] = $this->txt->sanitize($page["name"]);
            $data['cDescription'] = (string) $page["description"];
            $data['uID'] = $authorID;

            $newPage = $this->forumPage->add($this->ct, $data, $this->pt);

            $newPage->setAttribute('exclude_nav', 1);
            $newPage->setAttribute('hide_forum_sidebar', 1);

            // add xan_conversation block to page
            $data = [
                'displayMode' => 'flat',
                'orderBy' => 'date_asc',
                'enableCommentRating' => 1,
                'paginate' => 1,
                'itemsPerPage' => 10,
                'enablePosting' => 1,
                'displayPostingForm' => 'bottom',
            ];
            $cnvBlock = $newPage->addBlock(BlockType::getByHandle('xan_conversation'), $this->area, $data);

            $cnvMsgController = $this->app->make(ConversationMessageController::class);
            $msg = $cnvMsgController->add(true, [
                'authorID' => $authorID,
                'cID' => $newPage->getCollectionID(),
                'blockAreaHandle' => $this->area,
                'bID' => $cnvBlock->getBlockID(),
                'cnvID' => $cnvBlock->getController()->getConversationObject()->getConversationID(),
                'cnvMessageAuthorName' => $authorName,
                'cnvMessageAuthorEmail' => $authorEmail,
                'cnvMessageBody' => $cnvMessageBody,
            ]);
            if ($msg instanceof \Concrete\Core\Error\ErrorList\ErrorList) {
                $this->error->add($msg);
                $newPage->delete();

                return;
            } else {
                $msg->setMessageDateCreated($cnvMessageDateCreated);
            }
            // attach file to post
            if (!empty($post->attachments->attachment)) {
                foreach ($post->attachments->attachment as $attachment) {
                    $fileName = (string) trim($attachment->filename);
                    $fileUrl = (string) trim($attachment->filelink);
                    $this->attachFileToMessage($msg, $fileUrl, $fileName);
                }
            }
            //create new message for post
            if (!empty($post->messages->message)) {
                foreach ($post->messages->message as $message) {
                    $this->createNewMessage($message, $newPage, $cnvBlock, $cnvMsgController);
                }
            }
        }//end if user is not deleted
    }

    /**
     * create new message for post.
     *
     * @param $message
     * @param $newPage
     * @param $cnvBlock
     * @param $cnvMsgController
     */
    private function createNewMessage($message, $newPage, $cnvBlock, $cnvMsgController)
    {
        $authorName = (string) trim($message->cnvMessageAuthorName);
        //user is not deleted
        if ($authorID = $this->uir->getByName($authorName)->getUserObject()->getUserID()) {
            $authorEmail = (string) trim($message->cnvMessageAuthorEmail);
            $cnvMessageBody = $this->bbtohtml->tohtml((string) $message->cnvMessageBody);
            $cnvMessageBody = $this->bbCodeParser->parse($cnvMessageBody);
            $cnvMessageDateCreated = (string) trim($message->cnvMessageDateCreated);
            $data = [
                'authorID' => $authorID,
                'cID' => $newPage->getCollectionID(),
                'blockAreaHandle' => $this->area,
                'bID' => $cnvBlock->getBlockID(),
                'cnvID' => $cnvBlock->getController()->getConversationObject()->getConversationID(),
                'cnvMessageAuthorName' => $authorName,
                'cnvMessageAuthorEmail' => $authorEmail,
                'cnvMessageBody' => $cnvMessageBody,
            ];

            $msg = $cnvMsgController->add(true, $data);
            if ($msg instanceof \Concrete\Core\Error\ErrorList\ErrorList) {
                $this->error->add($msg);

                return;
            } else {
                $msg->setMessageDateCreated($cnvMessageDateCreated);
            }

            // attach file to message
            if (!empty($message->attachments->attachment)) {
                foreach ($message->attachments->attachment as $attachment) {
                    $fileName = (string) trim($attachment->filename);
                    $fileUrl = (string) trim($attachment->filelink);
                    $this->attachFileToMessage($msg, $fileUrl, $fileName);
                }
            }
        }// end if user is not deleted
    }

    /**
     * attach file to message.
     *
     * @param $msg
     * @param $url
     * @param $fileName
     */
    private function attachFileToMessage($msg, $url, $fileName)
    {
        $cf = Loader::helper('file');
        $fp = FilePermissions::getGlobal();
        $error = Loader::helper('validation/error');
        $fr = false;
        $r = new FileEditResponse();

        $file = Loader::helper('file');

        // load all the incoming fields into an array
        if (!function_exists('iconv_get_encoding')) {
            $error->add(t('Remote URL import requires the iconv extension enabled on your server.'));
        }

        if (!$error->has()) {
            $this_url = trim($url);

            // validate URL
            try {
                $request = new \Zend\Http\Request();
                $request->setUri($this_url);
                $client = new \Zend\Http\Client();
                $response = $client->dispatch($request);
            } catch (\Exception $e) {
                $error->add($e->getMessage());
            }
        }

        // if we haven't gotten any errors yet then try to process the form
        if (!$error->has()) {
            // try to D/L the provided file
            $request = new \Zend\Http\Request();
            $request->setUri($this_url);
            $client = new \Zend\Http\Client();
            $i = 0;
            do {
                $response = $client->dispatch($request);
                ++$i;
            } while (!$response->isSuccess() && $i < 10);
            if ($response->isSuccess()) {
                $fpath = $file->getTemporaryDirectory();
                $fname = $fileName;
                if (strlen($fname)) {
                    // write the downloaded file to a temporary location on disk
                    $handle = fopen($fpath . '/' . $fname, "w");
                    fwrite($handle, $response->getBody());
                    fclose($handle);

                    // import the file into concrete
                    if ($fp->canAddFileType($cf->getExtension($fname))) {
                        $folder = null;
                        if (isset($_POST['currentFolder'])) {
                            $node = \Concrete\Core\Tree\Node\Node::getByID($_POST['currentFolder']);
                            if ($node instanceof \Concrete\Core\Tree\Node\Type\FileFolder) {
                                $folder = $node;
                            }
                        }

                        if (!$fr && $folder) {
                            $fr = $folder;
                        }

                        $fi = new FileImporter();
                        $resp = $fi->import($fpath . '/' . $fname, $fname, $fr);
                    } else {
                        $resp = FileImporter::E_FILE_INVALID_EXTENSION;
                    }
                    if (!($resp instanceof \Concrete\Core\Entity\File\Version)) {
                        $error->add($fname . ': ' . FileImporter::getErrorMessage($resp));
                    } else {
                        if (!($fr instanceof \Concrete\Core\Entity\File\Version)) {
                            // we check $fr because we don't want to set it if we are replacing an existing file
                            $respf = $resp->getFile();
                            $msg->attachFile($respf);
                        }
                    }
                    // clean up the file
                    unlink($fpath . '/' . $fname);
                } else {
                    // could not figure out a file name
                    $error->add(t(/*i18n: %s is an URL*/
                        'Could not determine the name of the file at %s', h($this_url)));
                }
            } else {
                // warn that we couldn't download the file
                $error->add(t(/*i18n: %s is an URL*/
                    'There was an error downloading %s', h($this_url)));
            }
        }
        $r->setError($error);
    }
}
