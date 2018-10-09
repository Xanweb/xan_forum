<?php
namespace Concrete\Package\XanForum\Controller\SinglePage\Dashboard\System\Backup;

use Concrete\Core\Page\Controller\DashboardPageController;
use PageTemplate;
use User;
use Core;
use BlockType;
use Page;
use Concrete\Core\Page\Type\Type as CollectionType;
use Concrete\Core\User\UserInfo;

class Import extends DashboardPageController
{
    private $sendWelcomeMail;
    private $area;
    private $pt;
    private $ct;
    private $xml;

    /**
     * Import constructor.
     *
     * @param Page $c
     */
    public function __construct(Page $c)
    {
        parent::__construct($c);
    }

    public function view()
    {
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

    private function getOrCreateUser($xmlUserId)
    {
        $user = reset($this->xml->xpath('//user[@user_id=' . $xmlUserId . ']'));
        $mail = $user['mail_address'];
        $name = $user['name'];
        $country = $user['country'];
        $city = $user['city'];
        $info = $user['info'];

        $ui = Core::make('Concrete\Core\User\UserInfoRepository')->getByEmail($mail);
        if (!$ui) {
            $data = [
                'uName' => $name,
                'uEmail' => $mail,
            ];
            $ui = Core::make('user.registration')->create($data);

            $ui->setAttribute('country', $country);
            $ui->setAttribute('city', $city);
            $ui->setAttribute('info', $info);

            $this->sendWelcomeMail($ui);
        }

        return $ui->getUserID();
    }

    private function sendWelcomeMail(UserInfo $ui)
    {
        if (!$this->sendWelcomeMail) {
            return;
        }

        $mh = Core::make('helper/mail');

        $mh->addParameter('content', '<p>Willkommen bei der neuen concrete5 Community von Deutschland und der Schweiz</p><p>Nachdem beide Seite schon ein paar Jahre alt waren, haben wir uns entschlossen, einen gemeinsamen Auftritt zu starten. Zumal die geschriebene Sprache fast identisch ist, glauben wir, dass wir euch damit besser helfen können.</p><p>Was hat sich verändert? Neben dem neuen Layout haben wir primär die beiden Foren zusammengeführt. Sämtliche Beiträge von beiden Seiten sind neu hier zu finden! Damit wir das tun konnten, mussten wir die Benutzer neu anlegen und weil man Passwörter nie in einer lesbaren Form speichert, müsst ihr euer Passwort zurücksetzen. http://www.concrete5-cms.de/login</p><p>Wir hoffen ihr werden die Seite weiterhin rege nutzen und freuen uns mich euch die concrete5 Community weiter zu bringen.</p>');
        $mh->addParameter('link', 'http://www.concrete5-cms.de');
        /* @TODO switch between .ch and .de */
        $mh->addParameter('salutation', 'Hallo ' . $ui->getUserName());
        $mh->load('welcome_mail', 'xan_forum');
        $mh->to($ui->getUserEmail());
        $mh->sendMail();
    }

    public function merge_xml()
    {
        $categoryMap = [
            'Anwender' => 'Allgemeiner Support',
            'Chit-Chat' => 'Sonstiges',
            'Programmierung' => 'Erweiterungen / Themen',
            'Installation' => 'Installation / Aktualisierungen',
        ];

        $xml1 = simplexml_load_file($_FILES['xml1']['tmp_name']);
        $xml2 = simplexml_load_file($_FILES['xml2']['tmp_name']);

        $forumXml = new \SimpleXMLElement("<forum></forum>");

        // clone all users from xml1 into our new output document
        $doc = dom_import_simplexml($forumXml)->ownerDocument;

        $fragment = $doc->createDocumentFragment();
        $fragment->appendXML($xml1->users->asXML());
        $doc->documentElement->appendChild($fragment);

        // append all users from xml2, but avoid duplicates and fix user id
        $userIdMap = [];
        foreach ($xml2->users->user as $user) {
            // check if mail address exists in our first file
            $result = $xml1->xpath('//user[@mail_address=\'' . $user['mail_address'] . '\']')[0];

            if ($result) {
                $userIdMap[(string) $user['user_id']] = (string) $result['user_id'];
            } else {
                $newUser = $forumXml->users->addChild('user');
                $newUser['user_id'] = $user['user_id'] + 1000000;
                $newUser['name'] = $user['name'];
                $newUser['mail_address'] = $user['mail_address'];
                $newUser['country'] = $user['country'];
                $newUser['city'] = $user['city'];
                $newUser['info'] = $user['info'];

                $userIdMap[(string) $user['user_id']] = $newUser['user_id'];
            }
        }

        // copy topics
        $categories = $forumXml->addChild('categories');
        $files = [$xml1, $xml2];
        foreach ($files as $index => $xml) {
            foreach ($xml->categories->category as $category) {
                $categoryName = (string) $category['name'];
                if (array_key_exists($categoryName, $categoryMap)) {
                    $categoryName = $categoryMap[$categoryName];
                }

                // add category to output
                $result = $categories->xpath('category[@name=\'' . $categoryName . '\']');
                if (empty($result)) {
                    $newCategory = $categories->addChild('category');
                    $newCategory['name'] = $categoryName;
                } else {
                    $newCategory = $result[0];
                }

                // add topics to category
                foreach ($category->topic as $topic) {
                    $newTopic = $newCategory->addChild('topic');
                    $newTopic['name'] = $topic['name'];
                    $newTopic['date_created'] = $topic['date_created'];

                    // add answers
                    foreach ($topic->answer as $answer) {
                        $newAnswer = $newTopic->addChild('answer');

                        // map user ids for second file
                        if (0 == $index) {
                            $newAnswer['user_id'] = $answer['user_id'];
                        } else {
                            $newAnswer['user_id'] = isset($userIdMap[(string) $answer['user_id']]) ? $userIdMap[(string) $answer['user_id']] : $answer['user_id'];
                        }
                        $newAnswer['subject'] = $answer['subject'];
                        $newAnswer['ip_address'] = $answer['ip_address'];
                        $newAnswer['date_created'] = $answer['date_created'];
                        $newAnswer['is_topic'] = $answer['is_topic'];
                        if ($answer['file']) {
                            $newAnswer['file'] = $answer['file'];
                        }

                        $node = dom_import_simplexml($newAnswer);
                        $no = $node->ownerDocument;
                        $node->appendChild($no->createCDATASection((string) $answer));
                    }
                }
            }
        }

        header('Content-type: text/xml');
        echo $forumXml->asXML();
        die();
    }

    public function import_xml()
    {
        set_time_limit(60 * 60);

        $u = new User();
        $this->xml = simplexml_load_file($_FILES['xml']['tmp_name']);

        $forumPage = Page::getByID($_REQUEST['forumPage']);
        $this->ct = CollectionType::getByID($_REQUEST['ctID']);
        $this->pt = PageTemplate::getByHandle($_REQUEST['ptID']);
        $this->area = $_REQUEST['area'];
        $this->sendWelcomeMail = 1 == $_REQUEST['sendWelcomeMail'];

        foreach ($this->xml->categories->category as $category) {
            // add category pages
            $data['cName'] = $category['name'];
            $data['uID'] = $u->getUserID();
            $categoryPage = $forumPage->add($this->ct, $data, $this->pt);

            // add topic block
            $data = [
                'parentPageID' => $categoryPage->getCollectionID(),
                'area' => $this->area,
                'ctID' => $_REQUEST['ctID'],
                'ptID' => $_REQUEST['ptID'],
                'topicsPerPage' => 20,
            ];
            $categoryPage->addBlock(BlockType::getByHandle('xan_forum_topic'), $this->area, $data);

            // add topic pages
            foreach ($category->topic as $topic) {
                $data['cName'] = $topic['name'];
                if (isset($topic->answer[0])) {
                    $data['uID'] = $this->getOrCreateUser($topic->answer[0]['user_id']);
                } else {
                    $data['uID'] = $u->getUserID();
                }
                $topicPage = $categoryPage->add($this->ct, $data, $this->pt);

                // add answer blocks
                foreach ($topic->answer as $answer) {
                    $data = [];
                    $data['subject'] = $answer['subject'];
                    $data['text'] = (string) $answer;
                    $data['monitor'] = 0;
                    if (1 == $answer['is_topic']) {
                        $data['isTopic'] = 1;
                    }
                    $data['ipAddress'] = $answer['ip_address'];
                    $data['dtCreated'] = $answer['dtCreated'];

                    // @TODO
                    // $data['fID'] = $fID;

                    $data['userID'] = $this->getOrCreateUser($answer['user_id']);

                    $topicPage->addBlock(BlockType::getByHandle('xan_forum_message'), $this->area, $data);
                }

                // add answer form block
                $data = [];
                $topicPage->addBlock(BlockType::getByHandle('xan_forum_answer'), $this->area, $data);
            }
        }
    }
}
