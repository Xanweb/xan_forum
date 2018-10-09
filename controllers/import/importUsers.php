<?php
namespace Concrete\Package\XanForum\Controller\Import;

use Concrete\Core\Controller\Controller;
use Concrete\Core\User\User;
use Core;
use Config;
use PDO;
use XanForum\Utility\PasswordGenerator;

class ImportUsers extends Controller
{
    private $config;
    private $server;
    private $databaseName;
    private $userName;
    private $password;
    private $tableNameUsers;
    private $colsFromUsers;
    private $uir;
    private $pdo;

    public function run()
    {
        $this->init();
        $this->pdo = $this->databaseConnect($this->server, $this->databaseName, $this->userName, $this->password);
        $userManager = Core::make('user/registration');
        $exportUsers = $this->getUsers();
        $this->addUsers($exportUsers, $userManager);
    }

    /**
     * connect to database.
     *
     * @param $server
     * @param $databaseName
     * @param $userName
     * @param $password
     *
     * @return bool|PDO
     */
    private function databaseConnect($server, $databaseName, $userName, $password)
    {
        try {
            $pdo = new PDO('mysql:host=' . $server . ';dbname=' . $databaseName . ';charset=utf8', $userName, $password);
        } catch (Exeption $e) {
            die('Error : ' . $e->getMessage());

            return false;
        }

        return $pdo;
    }

    /**
     * Initialization of variables.
     */
    private function init()
    {
        $this->config = Config::get("importUsers");
        $this->server = $this->config["host"];
        $this->databaseName = $this->config["databaseName"];
        $this->userName = $this->config["user"];
        $this->password = $this->config["password"];
        $this->tableNameUsers = $this->config["configUsers"]["tableName"];
        $this->colsFromUsers = $this->config["configUsers"]["colsFromUsers"];
        $this->uir = Core::make('Concrete\Core\User\UserInfoRepository');
    }

    /**
     * get all users.
     *
     * @return mixed
     */
    private function getUsers()
    {
        $users = $this->pdo
            ->query("select " . implode(', ', $this->colsFromUsers) . " from " . $this->tableNameUsers)
            ->fetchALL();

        return $users;
    }

    private function addUsers($exportUsers, $userManager)
    {
        $pg = new PasswordGenerator();
        $pg->useNumbers(2);
        $pg->useSpecialChars(1);

        if (!empty($exportUsers)) {
            //if the first import users in database
            if (!is_object($this->uir->getByName($exportUsers[0]["username"]))) {
                foreach ($exportUsers as $exportUser) {
                    $pss = $pg->generatePassword(8);
                    $newUser = $userManager->create(["uName" => $exportUser["username"],
                        "uEmail" => $exportUser["email"],
                        "uPassword" => $pss,
                        "uPasswordConfirm" => $pss,
                        "uDateAdded" => date("Y-m-d H:i:s", $exportUser["regdate"]),
                        "uIsActive" => true,
                        "uIsValidated" => 0,
                    ])->getUserObject();
                    $this->sendMailToNewUser($newUser);
                }
            }
        }
    }

    /**
     * send mail to new user  for validate compte and change password.
     *
     * @param User $newUser
     */
    private function sendMailToNewUser(User $newUser)
    {
        $salutation = "";
        $content = "";
        $link = "";
        $mailService = Core::make('mail');
        $mailService->setTesting(false);
        $mailService->setSubject('');
        $mailService->addParameter('salutation', $salutation);
        $mailService->addParameter('content', $content);
        $mailService->addParameter('link', $link);
        $mailService->load('mail_account_validation', 'xan_forum');
        $mailService->from('c5dmin@xanweb.com', '');
        //$newUser->getUserInfoObject()->getUserEmail()
        $mailService->to("c5admin@xanweb.com", $newUser->getUserName());
        $mailService->sendMail();
    }
}
