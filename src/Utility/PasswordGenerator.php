<?php
namespace XanForum\Utility;

class PasswordGenerator
{
    private $vowels = 'aeiouAEIOU';
    private $consonants = 'bcdfghijklmnpqrstvwxyzBCDFGHIJKLMNPQRSTVWXYZ';
    private $numbers = '1234567890';
    private $specialChars = '@#$%^';
    private $flag = 1;
    private $numberLength = 0;
    private $specialCharsLength = 0;
    private $isUseNumbers = false;
    private $isUseSpecialChars = false;

    /**
     * Generate the password based on filter parameters.
     *
     * @param int $length
     *
     * @return string
     */
    public function generatePassword($length = 8)
    {
        $password = '';

        if (true === $this->isUseNumbers && $length >= $this->numberLength) {
            $length -= $this->numberLength;
        } else {
            $this->isUseNumbers = false;
        }

        if (true === $this->isUseSpecialChars && $length >= $this->specialCharsLength) {
            $length -= $this->specialCharsLength;
        } else {
            $this->isUseSpecialChars = false;
        }

        if (true === $this->isUseNumbers) {
            for ($count = 0; $count < $this->numberLength; ++$count) {
                $password .= $this->numbers[mt_rand(0, 9)];
            }
        }

        for ($count = 0; $count < $length; ++$count) {
            if ($count < $length / 2) {
                $password .= $this->lowerCaseChar();
            } else {
                $password .= $this->upperCaseChar();
            }
        }

        if (true === $this->isUseSpecialChars) {
            for ($count = 0; $count < $this->specialCharsLength; ++$count) {
                $password .= $this->specialChars[mt_rand(0, 4)];
            }
        }

        return $password;
    }

    /**
     * Set the length of number set and set active the isUseNumbers flag.
     *
     * @param int $length
     *
     * @return \RandomPasswordGenerator
     */
    public function useNumbers($length = 2)
    {
        $this->numberLength = $length;
        $this->isUseNumbers = true;

        return $this;
    }

    /**
     * Set the length of Special Chars set and set active the isUseSpecialChars flag.
     *
     * @param int $length
     *
     * @return \RandomPasswordGenerator
     */
    public function useSpecialChars($length = 1)
    {
        $this->specialCharsLength = $length;
        $this->isUseSpecialChars = true;

        return $this;
    }

    /**
     * Internal function
     *  It returns upper case characters of vowels or consonants.
     *
     * @return string
     */
    private function upperCaseChar()
    {
        if (1 === $this->flag) {
            $this->flag = 0;

            return $this->vowels[mt_rand(5, 9)];
        } else {
            $this->flag = 1;

            return $this->consonants[mt_rand(22, 43)];
        }
    }

    /**
     * Internal function
     *  It returns lower case characters of vowels or consonants.
     *
     * @return string
     */
    private function lowerCaseChar()
    {
        if (0 === $this->flag) {
            $this->flag = 1;

            return $this->vowels[mt_rand(0, 4)];
        } else {
            $this->flag = 0;

            return $this->consonants[mt_rand(0, 21)];
        }
    }
}
