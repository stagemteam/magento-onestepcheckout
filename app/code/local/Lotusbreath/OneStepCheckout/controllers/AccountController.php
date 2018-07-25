<?php
class Lotusbreath_OneStepCheckout_AccountController extends Mage_Core_Controller_Front_Action {


    public function forgotPasswordPostAction()
    {
        $email = (string) $this->getRequest()->getPost('email');
        $messages = array();
        $success = false;
        if ($email) {
            if (!Zend_Validate::is($email, 'EmailAddress')) {

                $messages[] = (Mage::helper('customer')->__('Invalid email address.'));

            }

            /** @var $customer Mage_Customer_Model_Customer */
            $customer = Mage::getModel('customer/customer')
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                ->loadByEmail($email);

            if ($customer->getId()) {
                try {
                    $newResetPasswordLinkToken =  Mage::helper('customer')->generateResetPasswordLinkToken();
                    $customer->changeResetPasswordLinkToken($newResetPasswordLinkToken);
                    $customer->sendPasswordResetConfirmationEmail();
                    $success = true;
                } catch (Exception $exception) {
                    $success = false;
                    $messages[] = (Mage::helper('customer')->__('Invalid email address.'));
                }
            }else{
                $messages[] = (Mage::helper('customer')->__('Invalid email address.'));
                $success = false;
            }
            $messages[] = Mage::helper('customer')->__('If there is an account associated with %s you will receive an email with a link to reset your password.',
                Mage::helper('customer')->escapeHtml($email));

        } else {
            $messages[] = (Mage::helper('customer')->__('Please enter your email.'));

        }
        echo json_encode(array(
            'success' => $success,
            'messages' => $messages
        ));
    }

    /**
     * Login action
     *
     */
    public function loginAction()
    {
        $session = Mage::getSingleton('customer/session');
        if ($session->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }
        $errorMessages = array();
        $success = false;
        if ($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getPost('login');

            if (!empty($login['username']) && !empty($login['password'])) {
                try {
                    $session->login($login['username'], $login['password']);
                    if ($session->getCustomer()->getIsJustConfirmed()) {
                        $this->_welcomeCustomer($session->getCustomer(), true);
                    }
                    $success = true;
                } catch (Mage_Core_Exception $e) {
                    switch ($e->getCode()) {
                        case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                            $value = Mage::helper('customer')->getEmailConfirmationUrl($login['username']);
                            $message = Mage::helper('customer')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', $value);
                            break;
                        case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                            $message = $e->getMessage();
                            break;
                        default:
                            $message = $e->getMessage();
                    }
                    //$session->addError($message);
                    $session->setUsername($login['username']);
                    $errorMessages[] = $message;
                } catch (Exception $e) {
                    // Mage::logException($e); // PA DSS violation: this exception log can disclose customer password
                }
            } else {
                $errorMessages[] = $this->__('Login and password are required.');
                //$session->addError($this->__('Login and password are required.'));
            }
        }
        echo json_encode(array(
            'success' => $success,
            'messages' => $errorMessages
        ));

    }

}

