<?php
/**
 * @Author Ben Ali Faker
 * @Engineer/ProjectManager
 * @Company Xanweb
 * Date: 10/05/17.
 */
namespace Concrete\Package\XanForum\Controller\SinglePage\Account;

use Concrete\Core\Page\Controller\PageController as BasicController;
use Concrete\Core\User\ValidationHash;
use URL;
use User;

class RemoveAccount extends BasicController
{
    /**
     *  type of validation hash dedicated for removing email.
     */
    const VALIDATION_TYPE = 1;

    public $helpers = ['html', 'form', 'text'];

    public function on_start()
    {
        $this->error = $this->app->make('error');
    }

    public function on_before_render()
    {
        $this->set('error', $this->error);
    }

    public function view()
    {
        $user = new \User();
        $userInfoObject = $user->getUserInfoObject();
        if (!$user->isRegistered()) {
            $this->redirect(URL::to('/login'));
        }
        $config = $this->app['config'];
        if ($this->getRequest()->isMethod('POST')) {
            $password = $this->getRequest()->post('uPassword');
            $currentPassword = $user->getUserInfoObject()->getUserPassword();
            $passwordConfirm = $this->getRequest()->post('uPasswordConfirm');
            $pw_is_valid_legacy = ($config->get('concrete.user.password.legacy_salt') && User::legacyEncryptPassword($password) == $currentPassword);
            $pw_is_valid = $pw_is_valid_legacy || $user->getUserPasswordHasher()->checkPassword($password, $currentPassword);
            if ($password != $passwordConfirm) {
                $this->error->add(t('Password and Password confirmation are not equals !!'));
            } elseif (!$pw_is_valid) {
                $this->error->add(t('Password mismatch'));
            }
            if (!$this->error->has()) {
                $uHash = ValidationHash::add($user->getUserID(), self::VALIDATION_TYPE, false);
                $mh = $this->app->make('mail');
                $fromEmail = (string) $config->get('concrete.email.validate_remove_account.address');
                if (strpos($fromEmail, '@')) {
                    $fromName = (string) $config->get('concrete.email.validate_remove_account.name');
                    if ('' === $fromName) {
                        $fromName = t('Validate Remove Account');
                    }
                    $mh->from($fromEmail, $fromName);
                }

                $mh->addParameter('uEmail', $userInfoObject->getUserEmail());
                $mh->addParameter('uHash', $uHash);
                $mh->addParameter('site', tc('SiteName', $config->get('concrete.site')));
                $mh->to($userInfoObject->getUserEmail());
                $mh->load('validate_remove_user_account_email', "xan_forum");
                $mh->sendMail();
                //$this->redirect('/register', 'register_success_validate', $rcID);
                $redirectMethod = 'register_success_validate';
                //desactivate user to be not able to logged in after sending email of drop account
                $userInfoObject->deactivate();
                $user->logout();
                $this->addSuccessPartiallyRemovingAccountMessage();
                $this->render("/account/remove_account_validation");
            }
        }
    }

    /**
     * Method that remove account definitively  after user click on email received.
     *
     * @param null|string $uHash
     */
    public function email_remove_account_validation($uHash = null)
    {
        if ($uHash) {
            $uID = ValidationHash::getUserID($uHash, self::VALIDATION_TYPE);
            if ($uID) {
                $user = User::getByUserID($uID);
                $user->getUserInfoObject()->delete();
                $this->addSuccessTotalRemovingAccountMessage();
            } else { //not validation hash exist in ouwer system
                $this->set('message_remove_account', t('No entry for your request exist to validate the definitely remove of your account  '));
            }
        } else {
            $this->redirect(URL::to('/page_not_found'));
        }
        $this->render("/account/remove_account_validation");
    }

    /**
     *  set message that will be shown to user after partial removing account.
     */
    private function addSuccessPartiallyRemovingAccountMessage()
    {
        $this->set('message_remove_account', t('Your Account is disabled and an email is sent to you to validate the definitely remove of your account'));
    }

    /**
     *  set message that will be shown to user after definitively removing account.
     */
    private function addSuccessTotalRemovingAccountMessage()
    {
        $this->set('message_remove_account', t('Your Account is definitely removed . thanks for you visit'));
    }
}
