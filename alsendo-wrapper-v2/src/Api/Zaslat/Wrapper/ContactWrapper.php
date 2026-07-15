<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper\Api\Zaslat\Wrapper;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alsendo\AlsendoWrapper\Api\Zaslat\Model\ZaslatContact;
use Alsendo\AlsendoWrapper\Model\Contact;

class ContactWrapper
{
    /**
     * @param Contact $contact
     *
     * @return ZaslatContact
     *
     * Wraps Contact into ZaslatContact
     */
    public static function wrap(Contact $contact): ZaslatContact
    {
        $zaslatContact = new ZaslatContact();
        $zaslatContact->id = $contact->id;
        if ($zaslatContact->id !== null) {
            return $zaslatContact;
        }
        $person = self::splitFullName($contact->contactPerson);
        $zaslatContact->firstname = $person['firstname'];
        $zaslatContact->surname = $person['lastname'];
        $zaslatContact->company = $contact->company;
        $zaslatContact->street = trim($contact->line1 . ' ' . $contact->line2);
        $zaslatContact->city = $contact->city;
        $zaslatContact->zip = $contact->postalCode;
        $zaslatContact->country = $contact->countryCode;
        $zaslatContact->phone = $contact->phone;
        $zaslatContact->email = $contact->email;

        return $zaslatContact;
    }

    /**
     * Splits a full name into a first name and last name.
     *
     * @param string|null $fullName The full name to be split. If null or empty, returns empty strings for both first and last names.
     *
     * @return array An associative array with keys 'firstname' and 'lastname'.
     *               If the full name contains a space, the part before the first space is considered the first name,
     *               and the part after is considered the last name.
     *               If no space is found, the entire full name is treated as the first name, with the last name being empty.
     */
    public static function splitFullName(?string $fullName): array
    {
        if (empty($fullName)) {
            return ['firstname' => '', 'lastname' => ''];
        }

        $spacePosition = strpos($fullName, ' ');

        if ($spacePosition === false) {
            return ['firstname' => $fullName, 'lastname' => ''];
        }

        $firstname = substr($fullName, 0, $spacePosition);
        $lastname = substr($fullName, $spacePosition + 1);

        return ['firstname' => $firstname, 'lastname' => $lastname];
    }
}
