<?php

use \MediaWiki\Auth\AuthManager;
use MediaWiki\MediaWikiServices;

/**
 * Description of Shibboleth auth class
 *
 * @author northway
 */
class Shibboleth extends PluggableAuth {

    /**
     * Override PluggableAuth authenticate function
     *
     * @param int|NULL $id
     * @param string $username
     * @param string $realname
     * @param string $email
     * @param string $errorMessage
     * @return boolean
     */
    public function authenticate(&$id, &$username, &$realname, &$email, &$errorMessage) {

        $id = null;
        $username = $this->getUsername();
        $realname = $this->getDisplayName();
        $email = $this->getEmail();

        if (isset($GLOBALS['wgShibboleth_GroupMap'])) {
            $this->checkGroupMap();
        }

        return true;
    }

    /**
     * Logout
     *
     * @param User $user
     * @return boolean
     */
    public function deauthenticate(User &$user) {

        session_destroy();

        header('Location: ' . $this->getLogoutURL());

        return true;
    }

    public function saveExtraAttributes($id) {

    }

    /**
     * Handle user privilages if it has one
     *
     * @param User $user
     */
    public static function populateGroups(User $user) {

        if ( method_exists( MediaWikiServices::class, 'getAuthManager' ) ) {
            // MediaWiki 1.35+
            $authManager = MediaWikiServices::getInstance()->getAuthManager();
        } else {
            $authManager = AuthManager::singleton();
        }
        $groups = $authManager->getAuthenticationSessionData('shib_attr');

        if (!empty($groups)) {
            $groups_array = explode(";", $groups);

            // Check 'sysop' in LocalSettings.php
            $sysop = $GLOBALS['wgShibboleth_GroupMap']['sysop'];

            if (in_array($sysop, $groups_array)) {
                $user->addGroup('sysop');
            } else {
                $user->removeGroup('sysop');
            }

            // Check 'bureaucrat' in LocalSettings.php
            $bureaucrat = $GLOBALS['wgShibboleth_GroupMap']['bureaucrat'];

            if (in_array($bureaucrat, $groups_array)) {
                $user->addGroup('bureaucrat');
            } else {
                $user->removeGroup('bureaucrat');
            }
        }
    }

    /**
     * Display name from Shibboleth
     *
     * @return string
     * @throws Exception
     */
    private function getDisplayName() {

        // wgShibboleth_DisplayName check in LocalSettings.php
        if (empty($GLOBALS['wgShibboleth_DisplayName'])) {
            throw new Exception(wfMessage('shibboleth-wg-empty-displayname')->plain());
        } else {
            $displayName = $GLOBALS['wgShibboleth_DisplayName'];
        }

        // Real name Shibboleth attribute check
        if (empty(filter_input(INPUT_SERVER, $displayName))) {
            return '';
        } else {
            return filter_input(INPUT_SERVER, $displayName);
        }
    }

    /**
     * Email address from Shibboleth
     *
     * @return string
     * @throws Exception
     */
    private function getEmail() {

        // wgShibboleth_Email check in LocalSettings.php
        if (empty($GLOBALS['wgShibboleth_Email'])) {
            throw new Exception(wfMessage('shibboleth-wg-empty-email')->plain());
        } else {
            $mail = $GLOBALS['wgShibboleth_Email'];
        }

        // E-mail shibboleth attribute check
        if (empty(filter_input(INPUT_SERVER, $mail))) {
            return '';
        } else {
            return filter_input(INPUT_SERVER, $mail);
        }
    }

    /**
     * Username from Shibboleth
     *
     * @return string
     * @throws Exception
     */
    private function getUsername() {

        // wgShibboleth_Username check in LocalSettings.php
        if (empty($GLOBALS['wgShibboleth_Username'])) {
            throw new Exception(wfMessage('shibboleth-wg-empty-username')->plain());
        } else {
            $user = $GLOBALS['wgShibboleth_Username'];
        }

        // Username shibboleth attribute check
        if (empty(filter_input(INPUT_SERVER, $user))) {
            throw new Exception(wfMessage('shibboleth-attr-empty-username')->plain());
        } else {

            $username = filter_input(INPUT_SERVER, $user);

            // If $username contains '@' replace it with '(AT)'
            if (strpos($username, '@') !== false) {
                $username = str_replace('@', '(AT)', $username);
            }

            // Uppercase the first letter of $username
            return ucfirst($username);
        }
    }

    private function checkGroupMap() {

        $attr_name = $GLOBALS['wgShibboleth_GroupMap']['attr_name'];

        if (empty($attr_name)) {
            throw new Exception(wfMessage('shibboleth-wg-empty-groupmap-attr')->plain());
        }

        $groups = filter_input(INPUT_SERVER, $attr_name);

        if (empty($groups)) {
            throw new Exception(wfMessage('shibboleth-attr-empty-groupmap-attr')->plain());
        }

        if ( method_exists( MediaWikiServices::class, 'getAuthManager' ) ) {
            // MediaWiki 1.35+
            $authManager = MediaWikiServices::getInstance()->getAuthManager();
        } else {
            $authManager = AuthManager::singleton();
        }
        $authManager->setAuthenticationSessionData('shib_attr', $groups);
    }

    private function getLogoutURL() {

        $base_url = $GLOBALS['wgShibboleth_Logout_Base_Url'];

        if (empty($base_url)) {
            throw new Exception(wfMessage('shib-attr-empty-logout-base-url')->plain());
        }

        $target_url = $GLOBALS['wgShibboleth_Logout_Target_Url'];

        if (empty($target_url)) {
            throw new Exception(wfMessage('shib-attr-empty-logout-target-url')->plain());
        }

        $logout_url = $base_url . '/Shibboleth.sso/Logout?return=' . $target_url;

        return $logout_url;
    }

}
