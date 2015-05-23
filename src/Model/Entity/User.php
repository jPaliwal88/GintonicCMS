<?php
/**
 * GintonicCMS : Full Stack Content Management System (http://cms.gintonicweb.com)
 * Copyright (c) Philippe Lafrance, Inc. (http://phillafrance.com)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Philippe Lafrance (http://phillafrance.com)
 * @link          http://cms.gintonicweb.com GintonicCMS Project
 * @since         0.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace GintonicCMS\Model\Entity;

use Cake\Auth\DefaultPasswordHasher;
use Cake\Core\Configure;
use Cake\Network\Email\Email;
use Cake\ORM\Entity;
use Cake\I18n\Time;

/**
 * Represents the User Entity.
 */
class User extends Entity
{
    protected $_accessible = [
        'password' => false,
        'token' => false,
        '*' => true
    ];
    protected $_virtual = ['full_name'];
    protected $_hidden = ['password'];

    /**
     * Take plaintext password and return valid Hash of that password.
     * 
     * @param string $password plaintext password string
     * @return string Hash password string
     */
    protected function _setPassword($password)
    {
        return (new DefaultPasswordHasher)->hash($password);
    }

    /**
     * Virtual filed for full name of user.
     * return the concated string of first and last name of user as Full Name.
     * 
     * @return boolean | string full name of user.
     */
    protected function _getFullName()
    {
        if (isset($this->_properties['first']) && isset($this->_properties['last'])) {
            return $this->_properties['first'] . '  ' . $this->_properties['last'];
        }
        return false;
    }

    /**
     * Return Query of File for given user id.
     * user id is taken as condition.
     * For example,
     * $user = $this->Users->get($userId);
     * $user->_getFiles();
     * 
     * @return Cake\ORM\Query $userFiles The amended query
     */
    protected function _getFiles()
    {
        $files = TableRegistry::get('Files');
        $userFiles = $files->find('all')
            ->where(['user_id' => $this->id])
            ->all();
        return $userFiles;
    }

    /**
     * Send Recovery Email to given email id.
     * For Example,
     * $user = $this->Users->get($userId);
     * $user->sendRecovery();
     * 
     * @return boolean True if email is send else False.
     */
    public function sendRecovery()
    {
        $email = new Email('default');
        $email->viewVars([
            'userId' => $this->id,
            'token' => $this->token
        ]);
        $email->template('GintonicCMS.forgot_password')
            ->emailFormat('html')
            ->to($this->email)
            ->from([Configure::read('admin_mail') => Configure::read('site_name')])
            ->subject('Forgot Password');
        return $email->send();
    }

    /**
     * Send Signup Email to given email id.
     * For Example,
     * $user = $this->Users->get($userId);
     * $user->sendSignup();
     * 
     * @return boolean True if email is send else False.
     */
    public function sendSignup()
    {
        $email = new Email();
        $email->profile('default');
        $email->viewVars([
            'userId' => $this->id,
            'token' => $this->token
        ]);
        $email->template('GintonicCMS.signup')
            ->emailFormat('html')
            ->to($this->email)
            ->from(Configure::read('admin_mail'))
            ->subject('Account validation');
        return $email->send();
    }

    /**
     * Send Verification Email to given email id after successfull Signup of user.
     * For Example,
     * $user = $this->Users->get($userId);
     * $user->sendVerification();
     * 
     * @return boolean True if email is send else False.
     */
    public function sendVerification()
    {
        $email = new Email('default');
        $email->viewVars([
            'userId' => $this->id,
            'token' => $this->token, 'userName' => $this->full_name
        ]);
        $email->template('GintonicCMS.resend_code')
            ->emailFormat('html')
            ->to($this->email)
            ->from([Configure::read('admin_mail') => Configure::read('site_name')])
            ->subject('Account validation');
        return $email->send();
    }

    /**
     * Update the Token when user send Varification mail again.
     */
    public function updateToken()
    {
        $this->token = md5(uniqid(rand(), true));
        $this->token_creation = date("Y-m-d H:i:s");
    }

    /**
     * Verify the Token recieved in url while changing the password
     * or validating the account.
     * 
     * @param string $token unique token string.
     * @return boolean return true if token is successfully verified else return false.
     */
    public function verify($token)
    {
        $time = new Time($this->token_creation);
        if ($this->token == $token && $time->wasWithinLast('+1 day')) {
            $this->verified = true;
        }
        return $this->verified;
    }
}
