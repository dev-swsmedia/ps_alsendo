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
use Alsendo\AlsendoWrapper\Json;
use Alsendo\AlsendoWrapper\Model\Contact;

class ContactListResponseWrapper
{
    /**
     * Maps an array of Zaslat API contact data to shared Contact objects.
     *
     * @param array $contactsData raw contact data from the Zaslat API
     *
     * @return Contact[] array of shared Contact objects
     */
    public static function wrap(array $contactsData): array
    {
        $contacts = [];

        foreach ($contactsData as $contactData) {
            $zaslatContact = Json::mapArrayToObject($contactData, ZaslatContact::class);
            $contacts[] = self::mapToContact($zaslatContact);
        }

        return $contacts;
    }

    private static function mapToContact(ZaslatContact $zaslatContact): Contact
    {
        $contact = new Contact();
        $contact->id = $zaslatContact->id;
        $contact->name = trim($zaslatContact->firstname . ' ' . $zaslatContact->surname);
        $contact->company = $zaslatContact->company;
        $contact->line1 = $zaslatContact->street;
        $contact->city = $zaslatContact->city;
        $contact->postalCode = $zaslatContact->zip;
        $contact->countryCode = $zaslatContact->country;
        $contact->phone = $zaslatContact->phone;
        $contact->email = $zaslatContact->email;

        return $contact;
    }
}
