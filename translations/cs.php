<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

global $_MODULE;
$_MODULE = [];

// alsendo.php
$_MODULE['<{alsendo}prestashop>alsendo_aa6d0ae531f7a902190acc25f4bddf64'] = 'Alsendo';
$_MODULE['<{alsendo}prestashop>alsendo_3613b8bcca28a880bafb959be8d88cd0'] = 'Modul pro dopravu pro PrestaShop 8.2.';

// CheckoutHook.php
$_MODULE['<{alsendo}prestashop>checkouthook_7a480ff532d89d4b742cf00a65c1bb0a'] = 'Vyberte prosím výdejní místo';

// bulk_send.tpl
$_MODULE['<{alsendo}prestashop>bulk_send_ac8cdfaa44d99ca5f114157f313beebc'] = 'Hromadné odesílání objednávek';
$_MODULE['<{alsendo}prestashop>bulk_send_56ce2db9f678db68f42d6e57ad5843bb'] = 'Odeslat všechny objednávky';
$_MODULE['<{alsendo}prestashop>bulk_send_276da25246b6d20d388856bedbfcf8e5'] = 'Opakovat neúspěšné';
$_MODULE['<{alsendo}prestashop>bulk_send_a7fd25adb508793a9c7e4b691d087924'] = 'Stáhnout všechny štítky (ZIP)';
$_MODULE['<{alsendo}prestashop>bulk_send_4146db2b1655a124fe5ec0a50faaeda4'] = 'Zrušit dávku';
$_MODULE['<{alsendo}prestashop>bulk_send_038ce3d5b1c7f49c5f59c6735390e987'] = 'Stav objednávek';
$_MODULE['<{alsendo}prestashop>bulk_send_eccde36fd88bd0b7b254bb6465d25387'] = 'Celkem objednávek';
$_MODULE['<{alsendo}prestashop>bulk_send_2d13df6f8b5e4c5af9f87e0dc39df69d'] = 'Čekající';
$_MODULE['<{alsendo}prestashop>bulk_send_30ae8fff8898dc197acd49d9c0797d20'] = 'Úspěšné';
$_MODULE['<{alsendo}prestashop>bulk_send_d7c8c85bf79bbe1b7188497c32c3b0ca'] = 'Neúspěšné';
$_MODULE['<{alsendo}prestashop>bulk_send_45e96c0a422ce8a1a6ec1bd5eb9625c6'] = 'Vybrat vše';
$_MODULE['<{alsendo}prestashop>bulk_send_27db6e195d7bd62909783ee7f4181962'] = 'Odstranit vybrané';
$_MODULE['<{alsendo}prestashop>bulk_send_e8e7d3e3afe601c4a8901d03de2579be'] = 'Obnovit stav';
$_MODULE['<{alsendo}prestashop>bulk_send_a240fa27925a635b08dc28c9e4f9216d'] = 'Objednávka';
$_MODULE['<{alsendo}prestashop>bulk_send_48974cc3df177e6b565c39aa155458cd'] = 'Doručovací adresa';
$_MODULE['<{alsendo}prestashop>bulk_send_8f7d63769d37ecccce91192a7be374cd'] = 'Šablona balíku';
$_MODULE['<{alsendo}prestashop>bulk_send_49ffd9480effff979143d2e8d38931c4'] = 'Způsob dopravy';
$_MODULE['<{alsendo}prestashop>bulk_send_836853db5923bd41e669cd161866c034'] = 'Detaily balíku';
$_MODULE['<{alsendo}prestashop>bulk_send_ec53a8c4f07baed5d8825072c89799be'] = 'Stav';
$_MODULE['<{alsendo}prestashop>bulk_send_3ed1478c2c45e3f2a4285624855a5f7a'] = 'Zpráva / Akce';
$_MODULE['<{alsendo}prestashop>bulk_send_c1ba75ea88b4130667156bee3269155b'] = 'JIŽ ODESLÁNO';
$_MODULE['<{alsendo}prestashop>bulk_send_9f935beb31030ad0d4d26126c0f39bf2'] = 'ZRUŠENO';
$_MODULE['<{alsendo}prestashop>bulk_send_d6c6fb473145755e97bbab2f56e08cd1'] = 'Upravit detaily objednávky (otevře v nové kartě)';
$_MODULE['<{alsendo}prestashop>bulk_send_7dce122004969d56ae2e0245cb754d35'] = 'Upravit';
$_MODULE['<{alsendo}prestashop>bulk_send_1ca9c7b5fb9b7a1272b2047d5f9a560c'] = 'Opakovat tuto objednávku';
$_MODULE['<{alsendo}prestashop>bulk_send_6327b4e59f58137083214a1fec358855'] = 'Opakovat';
$_MODULE['<{alsendo}prestashop>bulk_send_58594ab29c52590415e3dd254ae88250'] = 'Tato objednávka má přizpůsobené údaje o dopravě';
$_MODULE['<{alsendo}prestashop>bulk_send_c2808546f3e14d267d798f4e0e6f102e'] = 'Přizpůsobeno';
$_MODULE['<{alsendo}prestashop>bulk_send_390ca1d0395f2d88bc1968a01a4cb12a'] = 'Zpět na objednávky';
$_MODULE['<{alsendo}prestashop>bulk_send_dc053a0d03ecd66524b4afdd1e679e90'] = 'Odstranit vybrané objednávky z této dávky?';
$_MODULE['<{alsendo}prestashop>bulk_send_7b0fee4d957f4ffc0ed5abdd184062b7'] = 'Odesílání...';
$_MODULE['<{alsendo}prestashop>bulk_send_861fde1769999211eafa1e500b4f6e7e'] = 'Při odesílání objednávek došlo k chybě';
$_MODULE['<{alsendo}prestashop>bulk_send_31fc3e1e24dc41f26fe3e5221dccc7e3'] = 'Opravdu chcete opakovat neúspěšné objednávky?';
$_MODULE['<{alsendo}prestashop>bulk_send_e23fbe3ae8d99aaffe9428b0dd1f4ccc'] = 'Opakování...';
$_MODULE['<{alsendo}prestashop>bulk_send_504b1f47adea52a7dc7cd5f5e2490a68'] = 'Při opakování došlo k chybě';
$_MODULE['<{alsendo}prestashop>bulk_send_56c77a25c94b1f791bff4c3a4fedd5d7'] = 'Chyba opakování:';
$_MODULE['<{alsendo}prestashop>bulk_send_b13149c9aeadb80226408ebbecf82ecb'] = 'Příprava ZIP...';
$_MODULE['<{alsendo}prestashop>bulk_send_3f5456139301b237b68f8a14b5eb49c0'] = 'Opravdu chcete zrušit a smazat celou tuto dávku? Všechny odeslané zásilky budou zrušeny přes API kurýra a všechny záznamy budou smazány. Tím se objednávky vrátí do stavu před odesláním.';
$_MODULE['<{alsendo}prestashop>bulk_send_ef5ba1f8e7df68c8abf64ddeb2ff2a73'] = 'Rušení...';
$_MODULE['<{alsendo}prestashop>bulk_send_af89ecdebc18a44112458e75731bb108'] = 'Dávka úspěšně zrušena';
$_MODULE['<{alsendo}prestashop>bulk_send_43cb4c52ed3ccf37933cacdfaf31b053'] = 'Chyba při rušení dávky: ';
$_MODULE['<{alsendo}prestashop>bulk_send_233f84c721a4f4e02ee919c839fcc031'] = 'Při rušení dávky došlo k chybě';
$_MODULE['<{alsendo}prestashop>bulk_send_d4cee4ffd11e0042783bb8b4449113e2'] = 'Opravdu chcete zrušit tuto zásilku?';
$_MODULE['<{alsendo}prestashop>bulk_send_a149e85a44aeec9140e92733d9ed694e'] = 'Zrušeno';
$_MODULE['<{alsendo}prestashop>bulk_send_aee9784c03b80d38d3271cde2b252b8d'] = 'Neznámá chyba';

// order_alsendo_shipping.tpl
$_MODULE['<{alsendo}prestashop>order_alsendo_shipping_8248965a785887c2735def1b8cb4e34a'] = 'Doprava Alsendo';
$_MODULE['<{alsendo}prestashop>order_alsendo_shipping_73127ba010494f470f9734846ed8758e'] = 'Adresa odesílatele';
$_MODULE['<{alsendo}prestashop>order_alsendo_shipping_0c1ec80465825af171a2cb9df19ac8d7'] = 'Název šablony';
$_MODULE['<{alsendo}prestashop>order_alsendo_shipping_48974cc3df177e6b565c39aa155458cd'] = 'Doručovací adresa';
$_MODULE['<{alsendo}prestashop>order_alsendo_shipping_209802fb858e2c83205027dbbb5d9e6c'] = 'Balík';
$_MODULE['<{alsendo}prestashop>order_alsendo_shipping_f4843c1c797abf1a256c8802b6cd9f51'] = 'Rozměry';
$_MODULE['<{alsendo}prestashop>order_alsendo_shipping_8c489d0946f66d17d73f26366a4bf620'] = 'Hmotnost';
$_MODULE['<{alsendo}prestashop>order_alsendo_shipping_605f34d77de836854cfc7774eebdf3e5'] = 'Typ balíku';
$_MODULE['<{alsendo}prestashop>order_alsendo_shipping_f15c1cae7882448b3fb0404682e17e61'] = 'Obsah';
$_MODULE['<{alsendo}prestashop>order_alsendo_shipping_469ded86e4fce0e962b4d91cc7d426ce'] = 'Dobírka';
$_MODULE['<{alsendo}prestashop>order_alsendo_shipping_8588ddecedd66a0b359e8b92e111a183'] = 'Deklarovaná hodnota';
$_MODULE['<{alsendo}prestashop>order_alsendo_shipping_725d61f5b48658799f7ff32147110aa6'] = 'Výdejní místo';
$_MODULE['<{alsendo}prestashop>order_alsendo_shipping_f4aa54e989b34d653cf73b598f051ed4'] = 'ID místa';
$_MODULE['<{alsendo}prestashop>order_alsendo_shipping_49ee3087348e8d44e1feda1917443987'] = 'Název';
$_MODULE['<{alsendo}prestashop>order_alsendo_shipping_dd7bf230fde8d4836917806aff6a6b27'] = 'Adresa';
$_MODULE['<{alsendo}prestashop>order_alsendo_shipping_6bb311efd788bb4b3123896667e767a7'] = 'Zásilka';
$_MODULE['<{alsendo}prestashop>order_alsendo_shipping_5055d1a4444c630d6839f48ab48aef91'] = 'Kurýr';
$_MODULE['<{alsendo}prestashop>order_alsendo_shipping_0612c008c0ceac34b53b5ca6a5ac4814'] = 'ID služby';
$_MODULE['<{alsendo}prestashop>order_alsendo_shipping_1debf905ac528b3f0615cd379ced0d38'] = 'Typ vyzvednutí';
$_MODULE['<{alsendo}prestashop>order_alsendo_shipping_3601146c4e948c32b6424d2c0a7f0118'] = 'Cena';
$_MODULE['<{alsendo}prestashop>order_alsendo_shipping_f550a209cfc6427a4730d545e97fbd4b'] = 'Vybrané výdejní místo';
$_MODULE['<{alsendo}prestashop>order_alsendo_shipping_f8af4eebe2ba1364b4c297c36edd7d65'] = 'Sledovací číslo';
$_MODULE['<{alsendo}prestashop>order_alsendo_shipping_5fb63579fc981698f97d55bfecb213ea'] = 'Kopírovat';
$_MODULE['<{alsendo}prestashop>order_alsendo_shipping_ac5dbcf2a792f6c6408aaaf29d7fd0a8'] = 'Sledovací odkaz';
$_MODULE['<{alsendo}prestashop>order_alsendo_shipping_ec53a8c4f07baed5d8825072c89799be'] = 'Stav';
$_MODULE['<{alsendo}prestashop>order_alsendo_shipping_58beeb7a92b333d9f7406c166def1012'] = 'Odhadovaná cena';
$_MODULE['<{alsendo}prestashop>order_alsendo_shipping_dd2af6e2a5a7ed5f0ef190c3bcb1a78d'] = 'Pro tuto objednávku nebyla odeslána žádná zásilka.';
$_MODULE['<{alsendo}prestashop>order_alsendo_shipping_bf64c4e3f7554887c44f0cbe6e1a8094'] = 'Stáhnout štítek';
$_MODULE['<{alsendo}prestashop>order_alsendo_shipping_b84958d5b8f71e3515f67c909c727128'] = 'Zrušit zásilku';
$_MODULE['<{alsendo}prestashop>order_alsendo_shipping_278c491bdd8a53618c149c4ac790da34'] = 'Šablona';
$_MODULE['<{alsendo}prestashop>order_alsendo_shipping_5474b4ea073f8dacc9718cd00bd3332a'] = 'Rychlé odeslání';
$_MODULE['<{alsendo}prestashop>order_alsendo_shipping_2e55b8d2aa1b430bb341910a031b2ea5'] = 'Vytvořit zásilku';
$_MODULE['<{alsendo}prestashop>order_alsendo_shipping_69f4218063fe3746f90d68c31e61c1e1'] = 'Pro tuto objednávku nebyly nalezeny žádné údaje o dopravě Alsendo.';
// order_alsendo_shipping.tpl — JS strings
$_MODULE['<{alsendo}prestashop>order_alsendo_shipping_d65d796732067fde27f1d1958ac7b162'] = 'Odeslat tuto objednávku s vybraným nastavením šablony?';
$_MODULE['<{alsendo}prestashop>order_alsendo_shipping_7b0fee4d957f4ffc0ed5abdd184062b7'] = 'Odesílání...';
$_MODULE['<{alsendo}prestashop>order_alsendo_shipping_a82c5618f8ca911ae11a7fc2fc8e43e9'] = 'Zásilka byla úspěšně odeslána!';
$_MODULE['<{alsendo}prestashop>order_alsendo_shipping_b3e1f3f951a065d621dba454b2523365'] = 'Požadavek selhal';

// module_configuration.tpl
$_MODULE['<{alsendo}prestashop>module_configuration_ca59c45d8318875b9dd4a91e734173e4'] = 'Oblast a konfigurace API';
$_MODULE['<{alsendo}prestashop>module_configuration_40f5008a576b311a6e4c809482251c87'] = 'Vyberte oblast';
$_MODULE['<{alsendo}prestashop>module_configuration_8333cefdd340d4592700252a058ae01c'] = 'Testovací režim (použít testovací API)';
$_MODULE['<{alsendo}prestashop>module_configuration_9fd6ae4048e5377048b24977e35d0d60'] = 'Povolte pro použití testovacího prostředí pro API volání';
$_MODULE['<{alsendo}prestashop>module_configuration_b566ae1a87b8a34bf3679872e69da297'] = 'APP ID';
$_MODULE['<{alsendo}prestashop>module_configuration_27da61abce8ab5c2057eaf7149314744'] = 'APP Secret';
$_MODULE['<{alsendo}prestashop>module_configuration_d876ff8da67c3731ae25d8335a4168b4'] = 'API klíč';
$_MODULE['<{alsendo}prestashop>module_configuration_459a6f79ad9b13cbcb5f692d2cc7a94d'] = 'Token';
$_MODULE['<{alsendo}prestashop>module_configuration_76525f0f34b48475e5ca33f71d296f3b'] = 'Client ID';
$_MODULE['<{alsendo}prestashop>module_configuration_734082edf44417dd19cc65943aa65c36'] = 'Client Secret';
$_MODULE['<{alsendo}prestashop>module_configuration_8bbdc678b2253e9a673d5e2bbca0162f'] = 'Zaregistrujte svou OAuth aplikaci zde:';
$_MODULE['<{alsendo}prestashop>module_configuration_5f3abed05d06de542224bb08bb7a9a2d'] = 'Nastavte toto jako Redirect URL při registraci:';
$_MODULE['<{alsendo}prestashop>module_configuration_28def26707f7e7e3662a6415466e2ccc'] = 'Pro vývoj použijte';
$_MODULE['<{alsendo}prestashop>module_configuration_f4c2666f551357457ca5e507fa376b21'] = 'jako redirect URL při registraci nové aplikace. Po přesměrování na Google, 1) nahraďte';
$_MODULE['<{alsendo}prestashop>module_configuration_c9c31263cbc1490f98e9d4b059ec7a03'] = 'v URL odkazem výše, 2) nahraďte "?code" za "&code" a stiskněte Enter.';
$_MODULE['<{alsendo}prestashop>module_configuration_0fa973fcd324cf1615eb0771c08ba1f5'] = 'Autorizovat s Ecolet';
$_MODULE['<{alsendo}prestashop>module_configuration_2ec0d16e4ca169baedb9b2d50ec5c6d7'] = 'Připojeno';
$_MODULE['<{alsendo}prestashop>module_configuration_7b277018e43d41bc445731092b91547d'] = 'Nepřipojeno';
$_MODULE['<{alsendo}prestashop>module_configuration_08580e93520802cb11555ba3d1a7b842'] = 'Uložit konfiguraci';
$_MODULE['<{alsendo}prestashop>module_configuration_d1f953c9d544c352d1f47988da9067fa'] = 'Výchozí adresa odesílatele';
$_MODULE['<{alsendo}prestashop>module_configuration_3b9d276be4d96f4c72ae028b3ed9b38a'] = 'Vyberte šablonu';
$_MODULE['<{alsendo}prestashop>module_configuration_5fd5bc6e854bdf7a363f645ab537671a'] = 'Zadejte název šablony';
$_MODULE['<{alsendo}prestashop>module_configuration_c9cc8cce247e49bae79f15173ce97354'] = 'Uložit';
$_MODULE['<{alsendo}prestashop>module_configuration_ea4788705e6873b424c65e91c2846b19'] = 'Zrušit';
$_MODULE['<{alsendo}prestashop>module_configuration_dbbca4e481b619ba255818779292c329'] = 'Nová šablona';
$_MODULE['<{alsendo}prestashop>module_configuration_db4b32bbb2a136a8e8960d91c20dad28'] = 'Bez názvu';
$_MODULE['<{alsendo}prestashop>module_configuration_0f35945c0ec8ece14d52e5fb9fdb6572'] = 'Typ adresy';
$_MODULE['<{alsendo}prestashop>module_configuration_1c76cbfe21c6f44c1d1e59d54f3e4420'] = 'Firma';
$_MODULE['<{alsendo}prestashop>module_configuration_8cf04a9734132302f96da8e113e80ce5'] = 'Domov';
$_MODULE['<{alsendo}prestashop>module_configuration_c281f92b77ba329f692077d23636f5c9'] = 'Název firmy';
$_MODULE['<{alsendo}prestashop>module_configuration_20db0bfeecd8fe60533206a2b5e9891a'] = 'Křestní jméno';
$_MODULE['<{alsendo}prestashop>module_configuration_8d3f5eff9c40ee315d452392bed5309b'] = 'Příjmení';
$_MODULE['<{alsendo}prestashop>module_configuration_d61ebdd8a0c0cd57c22455e9f0918c65'] = 'Ulice';
$_MODULE['<{alsendo}prestashop>module_configuration_209a669be7ad9e1c11d0a9dcf453831b'] = 'Číslo popisné';
$_MODULE['<{alsendo}prestashop>module_configuration_5cdea0362d09eaf35436fd7a67990c8a'] = 'Číslo bytu';
$_MODULE['<{alsendo}prestashop>module_configuration_572ed696f21038e6cc6c86bb272a3222'] = 'PSČ';
$_MODULE['<{alsendo}prestashop>module_configuration_57d056ed0984166336b7879c2af3657f'] = 'Město';
$_MODULE['<{alsendo}prestashop>module_configuration_257548fcc33881767d79aa21a473a78a'] = 'Kontaktní osoba';
$_MODULE['<{alsendo}prestashop>module_configuration_1f8261d17452a959e013666c5df45e07'] = 'Telefonní číslo';
$_MODULE['<{alsendo}prestashop>module_configuration_b357b524e740bc85b9790a0712d84a30'] = 'E-mailová adresa';
$_MODULE['<{alsendo}prestashop>module_configuration_d26c078fbaa4300428fc1de134660f0b'] = 'Číslo bankovního účtu';
$_MODULE['<{alsendo}prestashop>module_configuration_06933067aafd48425d67bcb01bba5cb6'] = 'Aktualizovat';
$_MODULE['<{alsendo}prestashop>module_configuration_49fd98d50d344b14a51fbd9bf9811807'] = 'Nastavit jako výchozí';
$_MODULE['<{alsendo}prestashop>module_configuration_1063e38cb53d94d386f21227fcd84717'] = 'Smazat';
$_MODULE['<{alsendo}prestashop>module_configuration_5364894435562ff7cd1b0c146e523bdd'] = 'Výchozí šablona';
$_MODULE['<{alsendo}prestashop>module_configuration_3537ab82f293d660eb1c551d1e804bbe'] = 'Nastavení dopravy';
$_MODULE['<{alsendo}prestashop>module_configuration_519044510c97365906cb7e8370ee07db'] = 'Typ balíku';
$_MODULE['<{alsendo}prestashop>module_configuration_eb6d8ae6f20283755b339c0dc273988b'] = 'Standardní';
$_MODULE['<{alsendo}prestashop>module_configuration_0bc46d977c14befd806371daff5e7e70'] = 'Nestandardní';
$_MODULE['<{alsendo}prestashop>module_configuration_32954654ac8fe66a1d09be19001de2d4'] = 'Šířka';
$_MODULE['<{alsendo}prestashop>module_configuration_ba2a9c6c8c77e03f83ef8bf543612275'] = 'Délka';
$_MODULE['<{alsendo}prestashop>module_configuration_eec6c4bdbd339edf8cbea68becb85244'] = 'Výška';
$_MODULE['<{alsendo}prestashop>module_configuration_8c489d0946f66d17d73f26366a4bf620'] = 'Hmotnost';
$_MODULE['<{alsendo}prestashop>module_configuration_469ded86e4fce0e962b4d91cc7d426ce'] = 'Dobírka';
$_MODULE['<{alsendo}prestashop>module_configuration_70e212a5f3152f68a2364253e09c472c'] = '0 = bez dobírky';
$_MODULE['<{alsendo}prestashop>module_configuration_7703d35702a9e49be902e435b45a4796'] = 'Deklarovaná hodnota';
$_MODULE['<{alsendo}prestashop>module_configuration_a948726f2535d5c2d005bc53872bf378'] = 'Obsah balíku';
$_MODULE['<{alsendo}prestashop>module_configuration_28ccc213d1075a10ac0fd2a36221c1d0'] = 'Vložit tag';
$_MODULE['<{alsendo}prestashop>module_configuration_8aa321f577b2e8600fe524c92415db88'] = 'Vyberte tag...';
$_MODULE['<{alsendo}prestashop>module_configuration_ca2fb7f67355c05d2ffa963fac6e91e1'] = 'Typ vyzvednutí';
$_MODULE['<{alsendo}prestashop>module_configuration_00add3a90773716701f2986571dcce17'] = 'Doručit na místo';
$_MODULE['<{alsendo}prestashop>module_configuration_761e10575e150338f291facc28683073'] = 'Vyzvednutí kurýrem';
$_MODULE['<{alsendo}prestashop>module_configuration_eb9e7bb3ad4da8e2b67612b0dcd07c12'] = 'Bez vyzvednutí';
$_MODULE['<{alsendo}prestashop>module_configuration_7b60fd0dfb2b1fedd2d7152feb10ef3b'] = 'Na vyžádání';
$_MODULE['<{alsendo}prestashop>module_configuration_335874ad17d87f5736467a9b44575618'] = 'Příležitostné';
$_MODULE['<{alsendo}prestashop>module_configuration_61fab3ae9cca25149cf5230e166b1826'] = 'Výchozí hodiny vyzvednutí kurýrem';
$_MODULE['<{alsendo}prestashop>module_configuration_96fcb6acf775fa7fbdd053353b795b86'] = 'Chyba validace:';
$_MODULE['<{alsendo}prestashop>module_configuration_c90a34eb2fc3c9a9e55c76d95ca51c5c'] = 'Tyto hodiny budou automaticky použity pro Rychlé odeslání, Hromadné odeslání a Plný formulář, když je typ vyzvednutí KURÝR.';
$_MODULE['<{alsendo}prestashop>module_configuration_51ac4bf63a0c6a9cefa7ba69b4154ef1'] = 'Nastavení';
$_MODULE['<{alsendo}prestashop>module_configuration_689202409e48743b914713f96d93947c'] = 'Hodnota';
$_MODULE['<{alsendo}prestashop>module_configuration_a5d083fbfc0611e061663c6b1a8ba698'] = 'Omezení';
$_MODULE['<{alsendo}prestashop>module_configuration_cb3e8ed9941b5c674f7bc8022e829c74'] = 'Od (Čas začátku)';
$_MODULE['<{alsendo}prestashop>module_configuration_113226df2c0d131043ac081856360bfb'] = 'Minimum: 08:00';
$_MODULE['<{alsendo}prestashop>module_configuration_ea996875fbb8440f263599ab8ab42963'] = 'Minimum: 09:00';
$_MODULE['<{alsendo}prestashop>module_configuration_368ba7745fc6b4d1f31696979d1c33f9'] = 'Do (Čas konce)';
$_MODULE['<{alsendo}prestashop>module_configuration_c586d296e5b1029120429f96002d3ab4'] = 'Maximum: 17:00 (kvůli pracovní době kurýrů)';
$_MODULE['<{alsendo}prestashop>module_configuration_3b31c3fb3c507b79d3f86f2b1e3392f5'] = 'Požadavky:';
$_MODULE['<{alsendo}prestashop>module_configuration_9e6de9d5d6793bb10077d143d9cf045e'] = 'Čas začátku musí být v 08:00 nebo později';
$_MODULE['<{alsendo}prestashop>module_configuration_3d5e65a9a61e113c79d513d83ed96101'] = 'Čas začátku musí být v 09:00 nebo později';
$_MODULE['<{alsendo}prestashop>module_configuration_2077e9e5e93ffa10848aeb42df5ecedf'] = 'Čas konce musí být v 17:00 nebo dříve';
$_MODULE['<{alsendo}prestashop>module_configuration_57c0c24907ba472c6d4e1932262cc376'] = 'Minimální okno mezi začátkem a koncem: 2 hodiny';
$_MODULE['<{alsendo}prestashop>module_configuration_815d372c8ff544c6d29473c6093bd465'] = 'Čas konce musí být po čase začátku';
$_MODULE['<{alsendo}prestashop>module_configuration_4501cbc1e052c10d31801650864423d1'] = 'Uložit hodiny vyzvednutí';
$_MODULE['<{alsendo}prestashop>module_configuration_af307eeb6d70b14606bfa0c6efd33d5e'] = 'Další nastavení';
$_MODULE['<{alsendo}prestashop>module_configuration_934136d78b33793f2f21f7a8db40a412'] = 'Výchozí odesílací místa';
$_MODULE['<{alsendo}prestashop>module_configuration_9ac0a73b31e57009db5ccba74bcef58f'] = 'InPost';
$_MODULE['<{alsendo}prestashop>module_configuration_53c6f3cca240d98b5e648d93115a2426'] = 'DHL';
$_MODULE['<{alsendo}prestashop>module_configuration_25d0f5ff97b16c01a18e48536d5e0aa8'] = 'Pocztex';
$_MODULE['<{alsendo}prestashop>module_configuration_3b9031dce4fcf88b489a923963dd0c49'] = 'DPD';
$_MODULE['<{alsendo}prestashop>module_configuration_0e60ac85bc39f1e221d66a048ff164da'] = 'UPS';
$_MODULE['<{alsendo}prestashop>module_configuration_28a13ca17a23177e0666b0d4f5dd571f'] = 'Vybrat místo';
$_MODULE['<{alsendo}prestashop>module_configuration_e0626222614bdee31951d84c64e5e9ff'] = 'Vybrat';
$_MODULE['<{alsendo}prestashop>module_configuration_db0b91542abe6108142d2d47da5acbe4'] = 'Uložit další nastavení';
// module_configuration.tpl — Declared Value auto-fill
$_MODULE['<{alsendo}prestashop>module_configuration_8588ddecedd66a0b359e8b92e111a183'] = 'Deklarovaná hodnota';
$_MODULE['<{alsendo}prestashop>module_configuration_4799a93be3d28362d19096ab87afe2f3'] = 'Automaticky vyplnit deklarovanou hodnotu celkovou částkou objednávky';
$_MODULE['<{alsendo}prestashop>module_configuration_da68c704232cc4db11379cccb428beef'] = 'Při zapnutí: deklarovaná hodnota = celková částka objednávky (produkty + doprava). Pokud šablona balíku má vyšší deklarovanou hodnotu, použije se hodnota šablony.';
$_MODULE['<{alsendo}prestashop>module_configuration_5c586cf56f002ffecfd1b4ae3236a5ab'] = 'Při vypnutí: deklarovaná hodnota = 0 (pokud šablona balíku nenastaví jinou hodnotu).';

// shipping_methods.tpl
$_MODULE['<{alsendo}prestashop>shipping_methods_af22fc80f66213750302086471651553'] = 'Způsoby dopravy';
$_MODULE['<{alsendo}prestashop>shipping_methods_922a1520646d93eb585614dca18cd82c'] = 'Spravujte dostupné způsoby dopravy a přiřaďte je ke kurýrním službám Alsendo.';
$_MODULE['<{alsendo}prestashop>shipping_methods_2ff474db92bdfa6e65ce20169a8fbfd6'] = 'Název způsobu';
$_MODULE['<{alsendo}prestashop>shipping_methods_708f3158bbf37d2583e70a256143cd1a'] = 'Kurýrní služba';
$_MODULE['<{alsendo}prestashop>shipping_methods_3601146c4e948c32b6424d2c0a7f0118'] = 'Cena';
$_MODULE['<{alsendo}prestashop>shipping_methods_46f3ea056caa3126b91f3f70beea068c'] = 'Mapa';
$_MODULE['<{alsendo}prestashop>shipping_methods_4d3d769b812b6faa6b76e1a8abaece2d'] = 'Aktivní';
$_MODULE['<{alsendo}prestashop>shipping_methods_06df33001c1d7187fdd81ea1f5b277aa'] = 'Akce';
$_MODULE['<{alsendo}prestashop>shipping_methods_fa3a3fafbbca706b4ff47d2b485c034f'] = 'Jakákoli služba';
$_MODULE['<{alsendo}prestashop>shipping_methods_c9cc8cce247e49bae79f15173ce97354'] = 'Uložit';
$_MODULE['<{alsendo}prestashop>shipping_methods_f2a6c498fb90ee345d997f888fce3b18'] = 'Smazat';
$_MODULE['<{alsendo}prestashop>shipping_methods_ea4788705e6873b424c65e91c2846b19'] = 'Zrušit';
$_MODULE['<{alsendo}prestashop>shipping_methods_e327b7d7f88fe881b3ec703b4db8ff2f'] = 'Přidat způsob';
$_MODULE['<{alsendo}prestashop>shipping_methods_57efaeaf0943c67c44fafea443660002'] = 'Dostupné kurýrní služby';
$_MODULE['<{alsendo}prestashop>shipping_methods_6de445a203ca1bd1828efc20872db6bc'] = 'Zapněte/vypněte služby. Pouze aktivní služby se zobrazí v pokladně a v nabídkách.';
$_MODULE['<{alsendo}prestashop>shipping_methods_4bd693e2190db27b9cdaed28a3a43668'] = 'Zobrazit uložené služby';
$_MODULE['<{alsendo}prestashop>shipping_methods_76a68cb1bb7ea496134b2eff8552cd7e'] = 'Synchronizovat s Alsendo';
$_MODULE['<{alsendo}prestashop>shipping_methods_b718adec73e04ce3ec720dd11a06a308'] = 'ID';
$_MODULE['<{alsendo}prestashop>shipping_methods_46140fd4f90101f3beccfd3428bea873'] = 'Název služby';
$_MODULE['<{alsendo}prestashop>shipping_methods_ec136b444eede3bc85639fac0dd06229'] = 'Dodavatel';
$_MODULE['<{alsendo}prestashop>shipping_methods_205b01b3197cf06688f6ed574faf5664'] = 'Doba doručení';
$_MODULE['<{alsendo}prestashop>shipping_methods_e8d3a9b18c1fea682e5ae6cb38e828a1'] = 'Uložit služby';
$_MODULE['<{alsendo}prestashop>shipping_methods_62a5e490880a92eef74f167d9dc6dca0'] = 'Skrýt';
// shipping_methods.tpl — JS strings
$_MODULE['<{alsendo}prestashop>shipping_methods_54daea96f9f71edf8ad30604be8f1b86'] = 'Prosím zadejte název metody.';
$_MODULE['<{alsendo}prestashop>shipping_methods_f8bacfec9ade03b0f3584356b898fca8'] = 'Přidání selhalo';
$_MODULE['<{alsendo}prestashop>shipping_methods_e32a4e5887229cc3ce7412591de16cff'] = 'Uložení selhalo';
$_MODULE['<{alsendo}prestashop>shipping_methods_71a1c5f19c8e300d350f5f68c71e4559'] = 'Smazání selhalo';
$_MODULE['<{alsendo}prestashop>shipping_methods_d83e9632d60a4e40247b494dd859612b'] = 'Smazat tuto metodu?';
$_MODULE['<{alsendo}prestashop>shipping_methods_6d7d82d9c1d7f68064d14e673d53eb19'] = 'Aktualizace selhala';
$_MODULE['<{alsendo}prestashop>shipping_methods_f97d2eb0a66987899d02bb180936afa3'] = 'Chyba: ';
$_MODULE['<{alsendo}prestashop>shipping_methods_575f5f86d2c7ea113c6710c1037c4465'] = 'Ukládání...';
$_MODULE['<{alsendo}prestashop>shipping_methods_4dfcd4297d6dad8dbc1f6ad7b716b0da'] = 'Synchronizováno!';
$_MODULE['<{alsendo}prestashop>shipping_methods_01a3456434bc51347c1aad2cd30eeb9d'] = 'Chyba při načítání služeb: ';

// order_full_form.tpl
$_MODULE['<{alsendo}prestashop>order_full_form_ff7a4b88790e50e80ce734da7f95e978'] = 'Zásilka Alsendo pro objednávku';
$_MODULE['<{alsendo}prestashop>order_full_form_20dc6cb490ae4906a49cfcc9aaf928e9'] = 'Šablona odesílatele';
$_MODULE['<{alsendo}prestashop>order_full_form_0fded0cabb76dbe501b1f3c319f1de45'] = '(Žádné šablony nejsou k dispozici)';
$_MODULE['<{alsendo}prestashop>order_full_form_b03ac94200d08468ecf39773995c78d6'] = 'Vyberte šablonu odesílatele...';
$_MODULE['<{alsendo}prestashop>order_full_form_73127ba010494f470f9734846ed8758e'] = 'Adresa odesílatele';
$_MODULE['<{alsendo}prestashop>order_full_form_0f35945c0ec8ece14d52e5fb9fdb6572'] = 'Typ adresy';
$_MODULE['<{alsendo}prestashop>order_full_form_1c76cbfe21c6f44c1d1e59d54f3e4420'] = 'Firma';
$_MODULE['<{alsendo}prestashop>order_full_form_8cf04a9734132302f96da8e113e80ce5'] = 'Domov';
$_MODULE['<{alsendo}prestashop>order_full_form_c281f92b77ba329f692077d23636f5c9'] = 'Název firmy';
$_MODULE['<{alsendo}prestashop>order_full_form_f11b368cddfe37c47af9b9d91c6ba4f0'] = 'Celé jméno';
$_MODULE['<{alsendo}prestashop>order_full_form_d61ebdd8a0c0cd57c22455e9f0918c65'] = 'Ulice';
$_MODULE['<{alsendo}prestashop>order_full_form_209a669be7ad9e1c11d0a9dcf453831b'] = 'Číslo popisné';
$_MODULE['<{alsendo}prestashop>order_full_form_5cdea0362d09eaf35436fd7a67990c8a'] = 'Číslo bytu';
$_MODULE['<{alsendo}prestashop>order_full_form_59716c97497eb9694541f7c3d37b1a4d'] = 'Země';
$_MODULE['<{alsendo}prestashop>order_full_form_572ed696f21038e6cc6c86bb272a3222'] = 'PSČ';
$_MODULE['<{alsendo}prestashop>order_full_form_57d056ed0984166336b7879c2af3657f'] = 'Město';
$_MODULE['<{alsendo}prestashop>order_full_form_257548fcc33881767d79aa21a473a78a'] = 'Kontaktní osoba';
$_MODULE['<{alsendo}prestashop>order_full_form_bcc254b55c4a1babdf1dcb82c207506b'] = 'Telefon';
$_MODULE['<{alsendo}prestashop>order_full_form_ce8ae9da5b7cd6c3df2929543a9af92d'] = 'E-mail';
$_MODULE['<{alsendo}prestashop>order_full_form_d26c078fbaa4300428fc1de134660f0b'] = 'Číslo bankovního účtu';
$_MODULE['<{alsendo}prestashop>order_full_form_68a242517f0a82d394c03990b9693f1f'] = 'Adresa příjemce';
$_MODULE['<{alsendo}prestashop>order_full_form_bc910f8bdf70f29374f496f05be0330c'] = 'Křestní jméno';
$_MODULE['<{alsendo}prestashop>order_full_form_77587239bf4c54ea493c7033e1dbf636'] = 'Příjmení';
$_MODULE['<{alsendo}prestashop>order_full_form_080bd3ce22ddb62968378caded9c0116'] = 'Ulice a číslo';
$_MODULE['<{alsendo}prestashop>order_full_form_25f75488c91cb6c3bab92672e479619f'] = 'PSČ';
$_MODULE['<{alsendo}prestashop>order_full_form_dc25bf0bfd0fea4b766831e5604b8fab'] = 'Výdejní místo zákazníka (kde zákazník vyzvedává)';
$_MODULE['<{alsendo}prestashop>order_full_form_8f7d63769d37ecccce91192a7be374cd'] = 'Šablona balíku';
$_MODULE['<{alsendo}prestashop>order_full_form_85cba971ce6efb9e7035fd891df211fb'] = 'Vyberte šablonu balíku...';
$_MODULE['<{alsendo}prestashop>order_full_form_836853db5923bd41e669cd161866c034'] = 'Detaily balíku';
$_MODULE['<{alsendo}prestashop>order_full_form_519044510c97365906cb7e8370ee07db'] = 'Typ balíku';
$_MODULE['<{alsendo}prestashop>order_full_form_eb6d8ae6f20283755b339c0dc273988b'] = 'Standardní';
$_MODULE['<{alsendo}prestashop>order_full_form_0bc46d977c14befd806371daff5e7e70'] = 'Nestandardní';
$_MODULE['<{alsendo}prestashop>order_full_form_411b77de7f1e317029408e321dab13a2'] = 'Nestandardní zásilka';
$_MODULE['<{alsendo}prestashop>order_full_form_3d0ed3e9145be53d081d10fe37c93290'] = 'Šířka (cm)';
$_MODULE['<{alsendo}prestashop>order_full_form_d39e1498c3bd289bb7ed63f790adfb0d'] = 'Délka (cm)';
$_MODULE['<{alsendo}prestashop>order_full_form_20fbaa7c1bf32aa91ed46514737a0687'] = 'Výška (cm)';
$_MODULE['<{alsendo}prestashop>order_full_form_91721604210524b7051d99c4c8478715'] = 'Hmotnost (kg)';
$_MODULE['<{alsendo}prestashop>order_full_form_f15c1cae7882448b3fb0404682e17e61'] = 'Obsah';
$_MODULE['<{alsendo}prestashop>order_full_form_28ccc213d1075a10ac0fd2a36221c1d0'] = 'Vložit tag';
$_MODULE['<{alsendo}prestashop>order_full_form_8aa321f577b2e8600fe524c92415db88'] = 'Vyberte tag...';
$_MODULE['<{alsendo}prestashop>order_full_form_526521a6501d201ffd502a6369019929'] = 'Vlastní textová hodnota';
$_MODULE['<{alsendo}prestashop>order_full_form_f4f909d3f07b338ce9ea367144fd3ccc'] = 'Zadejte vlastní text';
$_MODULE['<{alsendo}prestashop>order_full_form_469ded86e4fce0e962b4d91cc7d426ce'] = 'Dobírka';
$_MODULE['<{alsendo}prestashop>order_full_form_8588ddecedd66a0b359e8b92e111a183'] = 'Deklarovaná hodnota';
$_MODULE['<{alsendo}prestashop>order_full_form_3cbf8c051abb64522ac46ab771de2472'] = 'Detaily zásilky';
$_MODULE['<{alsendo}prestashop>order_full_form_49ffd9480effff979143d2e8d38931c4'] = 'Způsob dopravy';
$_MODULE['<{alsendo}prestashop>order_full_form_3601146c4e948c32b6424d2c0a7f0118'] = 'Cena';
$_MODULE['<{alsendo}prestashop>order_full_form_88bb3187fb7cb9fd47d458fd8223b052'] = 'Získat cenovou nabídku';
$_MODULE['<{alsendo}prestashop>order_full_form_1debf905ac528b3f0615cd379ced0d38'] = 'Typ vyzvednutí';
$_MODULE['<{alsendo}prestashop>order_full_form_6f177b2d7c4ea196e0f2ba95b4a48092'] = 'Preferované datum vyzvednutí';
$_MODULE['<{alsendo}prestashop>order_full_form_822bfcc4cba6214aac562cd187672c20'] = 'Nemůže být v minulosti';
$_MODULE['<{alsendo}prestashop>order_full_form_740d1c10e297d2d161ca882162f7f9e8'] = 'Vyberte dnešní nebo budoucí datum vyzvednutí kurýrem';
$_MODULE['<{alsendo}prestashop>order_full_form_691e60a534b3323c0232891bf9d4802b'] = 'Preferované hodiny vyzvednutí';
$_MODULE['<{alsendo}prestashop>order_full_form_5da618e8e4b89c66fe86e32cdafde142'] = 'Od';
$_MODULE['<{alsendo}prestashop>order_full_form_e12167aa0a7698e6ebc92b4ce3909b53'] = 'Do';
$_MODULE['<{alsendo}prestashop>order_full_form_0711b4ad556c779ebb9044f4bffdbf46'] = 'Odesílací místo prodejce (kde VY odesíláte)';
$_MODULE['<{alsendo}prestashop>order_full_form_379b83e36d45431e5c0e8726970f9b08'] = 'Vybrat z mapy';
$_MODULE['<{alsendo}prestashop>order_full_form_f4ec5f57bd4d31b803312d873be40da9'] = 'Změnit';
$_MODULE['<{alsendo}prestashop>order_full_form_dc30bc0c7914db5918da4263fce93ad2'] = 'Vymazat';
$_MODULE['<{alsendo}prestashop>order_full_form_5f711c836bc7246982eb3189e6aed85b'] = 'Nebyl zvolen zákaznický odběrný bod';
$_MODULE['<{alsendo}prestashop>order_full_form_836f8dcebca9d3dfce7a825ab11fb6c4'] = 'Vybrat odběrný bod';
$_MODULE['<{alsendo}prestashop>order_full_form_c9cc8cce247e49bae79f15173ce97354'] = 'Uložit';
$_MODULE['<{alsendo}prestashop>order_full_form_6825d4ad506a936ec4cef0c279f7ca1b'] = 'Odeslat zásilku';
$_MODULE['<{alsendo}prestashop>order_full_form_9c59b445df0f2f1cccfab262eb91f020'] = 'Odeslání přes výdejní místo';
$_MODULE['<{alsendo}prestashop>order_full_form_6927a6b63a70dcf6390ed632d785b8a0'] = 'Odevzdat balík na pobočce místo vyzvednutí kurýrem';
$_MODULE['<{alsendo}prestashop>order_full_form_49cdde4c74d70431b419e19db3aa0903'] = 'Požádat o vyzvednutí kurýrem';
$_MODULE['<{alsendo}prestashop>order_full_form_0b6497df6c7f8c3839fbc1bd8c0e7663'] = 'Kód banky';
$_MODULE['<{alsendo}prestashop>order_full_form_09db8aba136a1b65d3d3ae54e2c850a1'] = 'IBAN číslo bankovního účtu';

// sender_templates.tpl
$_MODULE['<{alsendo}prestashop>sender_templates_fc1a56273881dc4fe7f89c0f371e8dda'] = 'Výchozí šablony odesílatele';
$_MODULE['<{alsendo}prestashop>sender_templates_c1d7b8c998202c0454ebf4f6e8129c29'] = 'Vytvářejte a spravujte šablony adres odesílatele. Můžete vytvořit více šablon a jednu nastavit jako výchozí.';
$_MODULE['<{alsendo}prestashop>sender_templates_05f05f48915ab32c322f3f99c47640fc'] = 'Název šablony';
$_MODULE['<{alsendo}prestashop>sender_templates_7361647c8491c588a2949728cacbea5e'] = 'Jméno prodejce';
$_MODULE['<{alsendo}prestashop>sender_templates_dd7bf230fde8d4836917806aff6a6b27'] = 'Adresa';
$_MODULE['<{alsendo}prestashop>sender_templates_bbaff12800505b22a853e8b7f4eb6a22'] = 'Kontakt';
$_MODULE['<{alsendo}prestashop>sender_templates_7a1920d61156abc05a60135aefe8bc67'] = 'Výchozí';
$_MODULE['<{alsendo}prestashop>sender_templates_06df33001c1d7187fdd81ea1f5b277aa'] = 'Akce';
$_MODULE['<{alsendo}prestashop>sender_templates_93cba07454f06a4a960172bbd6e2a435'] = 'Ano';
$_MODULE['<{alsendo}prestashop>sender_templates_ffee71a61dfb978efbca62128a312bc1'] = 'Nastavit jako výchozí';
$_MODULE['<{alsendo}prestashop>sender_templates_7dce122004969d56ae2e0245cb754d35'] = 'Upravit';
$_MODULE['<{alsendo}prestashop>sender_templates_f2a6c498fb90ee345d997f888fce3b18'] = 'Smazat';
$_MODULE['<{alsendo}prestashop>sender_templates_13c43a7637602dc30a65fda08e6aa93d'] = 'Nebyly nalezeny žádné šablony. Vytvořte svou první šablonu.';
$_MODULE['<{alsendo}prestashop>sender_templates_ae2b83a081959fff7ab2e96f4ce972d1'] = 'Přidat novou šablonu';
$_MODULE['<{alsendo}prestashop>sender_templates_20dc6cb490ae4906a49cfcc9aaf928e9'] = 'Šablona odesílatele';
$_MODULE['<{alsendo}prestashop>sender_templates_0c1ec80465825af171a2cb9df19ac8d7'] = 'Název šablony';
$_MODULE['<{alsendo}prestashop>sender_templates_1961116c87f40d9e9966586b58f5f341'] = 'Typ adresy';
$_MODULE['<{alsendo}prestashop>sender_templates_fe0a49294d6dfd6c13ce312af9d536a2'] = '-- Vyberte --';
$_MODULE['<{alsendo}prestashop>sender_templates_9f5d4a26a690325988786ced21199e95'] = 'Jméno prodejce';
$_MODULE['<{alsendo}prestashop>sender_templates_bc910f8bdf70f29374f496f05be0330c'] = 'Křestní jméno';
$_MODULE['<{alsendo}prestashop>sender_templates_77587239bf4c54ea493c7033e1dbf636'] = 'Příjmení';
$_MODULE['<{alsendo}prestashop>sender_templates_d61ebdd8a0c0cd57c22455e9f0918c65'] = 'Ulice';
$_MODULE['<{alsendo}prestashop>sender_templates_070c051179746c6932db988f0a52b282'] = 'Číslo popisné';
$_MODULE['<{alsendo}prestashop>sender_templates_368fd64e16ace434535e976906a2f08a'] = 'Číslo bytu';
$_MODULE['<{alsendo}prestashop>sender_templates_25f75488c91cb6c3bab92672e479619f'] = 'PSČ';
$_MODULE['<{alsendo}prestashop>sender_templates_57d056ed0984166336b7879c2af3657f'] = 'Město';
$_MODULE['<{alsendo}prestashop>sender_templates_59716c97497eb9694541f7c3d37b1a4d'] = 'Země';
$_MODULE['<{alsendo}prestashop>sender_templates_790a8c9ddb09e77702aea1afff482ea1'] = 'Kontaktní osoba';
$_MODULE['<{alsendo}prestashop>sender_templates_1e4dbc7eaa78468a3bc1448a3d68d906'] = 'Telefonní číslo';
$_MODULE['<{alsendo}prestashop>sender_templates_ce8ae9da5b7cd6c3df2929543a9af92d'] = 'E-mail';
$_MODULE['<{alsendo}prestashop>sender_templates_f67f3f5332260d9c2e9a16585237a727'] = 'Číslo bankovního účtu (IBAN)';
$_MODULE['<{alsendo}prestashop>sender_templates_ea4788705e6873b424c65e91c2846b19'] = 'Zrušit';
$_MODULE['<{alsendo}prestashop>sender_templates_c9cc8cce247e49bae79f15173ce97354'] = 'Uložit';
$_MODULE['<{alsendo}prestashop>sender_templates_358f5806311bf0fe26bf7a4e0077e958'] = 'Přidat novou šablonu odesílatele';
$_MODULE['<{alsendo}prestashop>sender_templates_05827482df03fb04309d8db24f3e94e5'] = 'Upravit šablonu odesílatele';
$_MODULE['<{alsendo}prestashop>sender_templates_3da44b906269817f50ef58ea29f5f38d'] = 'Opravdu chcete smazat tuto šablonu?';

// info_modal.tpl
$_MODULE['<{alsendo}prestashop>info_modal_e0aa021e21dddbd6d8cecec71e9cf564'] = 'OK';

// carrier_map.tpl
$_MODULE['<{alsendo}prestashop>carrier_map_4c6d81fa3c64f41e61b4f30f54b80f1f'] = 'Vybrané výdejní místo';
$_MODULE['<{alsendo}prestashop>carrier_map_f8c2a0da1eb8c6ccbbdfda491be7e5d6'] = 'Vybrat výdejní místo';
$_MODULE['<{alsendo}prestashop>carrier_map_91412465ea9169dfd901dd5e7c96dd99'] = 'Načítání...';
$_MODULE['<{alsendo}prestashop>carrier_map_77025a7e76aed5a13359c4700622b859'] = 'Nevybráno výdejní místo';
$_MODULE['<{alsendo}prestashop>carrier_map_836f8dcebca9d3dfce7a825ab11fb6c4'] = 'Vyberte výdejní místo';
$_MODULE['<{alsendo}prestashop>carrier_map_7a480ff532d89d4b742cf00a65c1bb0a'] = 'Vyberte prosím výdejní místo';

// bulk_send_button.tpl
$_MODULE['<{alsendo}prestashop>bulk_send_button_9c4b5ea03d0bcd7bafabda4a4d35b5fd'] = 'Hromadně odeslat vybrané';
$_MODULE['<{alsendo}prestashop>bulk_send_button_a1c3470a944b9625cfb924fd15c8bdbf'] = 'Prosím vyberte alespoň jednu objednávku';

// module_configuration.tpl — map translations for JS
$_MODULE['<{alsendo}prestashop>module_configuration_5c4a33d265c4d2d060afc9d3459eb1d9'] = 'Načítání mapy...';
$_MODULE['<{alsendo}prestashop>module_configuration_8987fee3a3ddaac24443185e9c490e1b'] = 'Widget mapy se nepodařilo načíst';
$_MODULE['<{alsendo}prestashop>module_configuration_d3d2e617335f08df83599665eef8a418'] = 'Zavřít';
$_MODULE['<{alsendo}prestashop>module_configuration_f915a95e609bbd517a8a1e7bdcceef37'] = 'Zkusit znovu';

// carrier_map.tpl — map translations for JS
$_MODULE['<{alsendo}prestashop>carrier_map_5c4a33d265c4d2d060afc9d3459eb1d9'] = 'Načítání mapy...';
$_MODULE['<{alsendo}prestashop>carrier_map_8987fee3a3ddaac24443185e9c490e1b'] = 'Widget mapy se nepodařilo načíst';
$_MODULE['<{alsendo}prestashop>carrier_map_d3d2e617335f08df83599665eef8a418'] = 'Zavřít';
$_MODULE['<{alsendo}prestashop>carrier_map_f915a95e609bbd517a8a1e7bdcceef37'] = 'Zkusit znovu';

$_MODULE['<{alsendo}prestashop>adminalsendoordercontroller_00add3a90773716701f2986571dcce17'] = 'Doručení na výdejní místo';
$_MODULE['<{alsendo}prestashop>adminalsendoordercontroller_761e10575e150338f291facc28683073'] = 'Vyzvednutí kurýrem';
$_MODULE['<{alsendo}prestashop>adminalsendoordercontroller_eb9e7bb3ad4da8e2b67612b0dcd07c12'] = 'Bez vyzvednutí';
$_MODULE['<{alsendo}prestashop>adminalsendoordercontroller_335874ad17d87f5736467a9b44575618'] = 'Příležitostné';
$_MODULE['<{alsendo}prestashop>adminalsendoordercontroller_7b60fd0dfb2b1fedd2d7152feb10ef3b'] = 'Na vyžádání';
$_MODULE['<{alsendo}prestashop>adminalsendoordercontroller_8baa508c34450ffa61320110ffac05a6'] = 'Štítek od kurýra';
$_MODULE['<{alsendo}prestashop>adminalsendoordercontroller_428246aa11abbb4c1147ecd2f0a69db7'] = 'Vlastní štítek (podání na pobočce)';
// AdminOrderHook - pickup type labels for side panel
$_MODULE['<{alsendo}prestashop>adminorderhook_00add3a90773716701f2986571dcce17'] = 'Doručení na výdejní místo';
$_MODULE['<{alsendo}prestashop>adminorderhook_761e10575e150338f291facc28683073'] = 'Vyzvednutí kurýrem';
$_MODULE['<{alsendo}prestashop>adminorderhook_eb9e7bb3ad4da8e2b67612b0dcd07c12'] = 'Bez vyzvednutí';
$_MODULE['<{alsendo}prestashop>adminorderhook_8baa508c34450ffa61320110ffac05a6'] = 'Štítek od kurýra';
$_MODULE['<{alsendo}prestashop>adminorderhook_428246aa11abbb4c1147ecd2f0a69db7'] = 'Vlastní štítek (podání na pobočce)';
$_MODULE['<{alsendo}prestashop>adminalsendoordercontroller_a1c3470a944b9625cfb924fd15c8bdbf'] = 'Vyberte alespoň jednu objednávku';
$_MODULE['<{alsendo}prestashop>adminalsendobulksendcontroller_180ef6dd5aee210e5aea360efa88d62a'] = 'Nebyly vybrány žádné objednávky';
$_MODULE['<{alsendo}prestashop>adminalsendobulksendcontroller_8e61db4c47ed369fc00a5cae2bd4d084'] = 'Dávka nebyla nalezena';
$_MODULE['<{alsendo}prestashop>adminalsendobulksendcontroller_7e28fc9ff91a94e318a2f5b46692311e'] = 'V dávce nejsou žádné objednávky';
$_MODULE['<{alsendo}prestashop>adminalsendobulksendcontroller_e6ba362daa0381ef818b31fc1adcec0e'] = 'Nebyly poskytnuty žádné objednávky';
$_MODULE['<{alsendo}prestashop>adminalsendobulksendcontroller_2235f25f551b97caa3d8751837867317'] = 'Objednávky nelze zpracovat';

// Common messages
$_MODULE['<{alsendo}prestashop>module_configuration_db8ecb61f23a81a38b2c83858269f951'] = 'Uloženo!';
$_MODULE['<{alsendo}prestashop>module_configuration_902b0d55fddef6f8d651fe1035b7d4bd'] = 'Chyba';
$_MODULE['<{alsendo}prestashop>adminalsendomoduleconfigurationcontroller_ad92169e95e13b3fcd8bad5afedbd49b'] = 'Chyba ověření';
$_MODULE['<{alsendo}prestashop>shipping_methods_db8ecb61f23a81a38b2c83858269f951'] = 'Uloženo!';

// Validation messages
$_MODULE['<{alsendo}prestashop>adminalsendomoduleconfigurationcontroller_50a8f2e323389d248c923321032ba78e'] = 'Neplatný region';
$_MODULE['<{alsendo}prestashop>adminalsendomoduleconfigurationcontroller_c4dfd06a23babe1bbf3e180eb2f2d7a8'] = 'Neznámá akce';
$_MODULE['<{alsendo}prestashop>adminalsendomoduleconfigurationcontroller_9c4e90e47bb83bc621913cea936ef9bd'] = 'Čas zahájení vyzvednutí nemůže být dříve než 08:00';
$_MODULE['<{alsendo}prestashop>adminalsendomoduleconfigurationcontroller_3393561d4f7cdf5a5d8eae6e488614a8'] = 'Čas zahájení vyzvednutí nemůže být dříve než 09:00';
$_MODULE['<{alsendo}prestashop>adminalsendomoduleconfigurationcontroller_7d82e224c5ffbc0163aa530fdc15b79e'] = 'Čas ukončení vyzvednutí nemůže být později než 17:00';
$_MODULE['<{alsendo}prestashop>adminalsendomoduleconfigurationcontroller_b05266d1ab43fed608c145486b323e1a'] = 'Čas ukončení musí být po čase zahájení';
$_MODULE['<{alsendo}prestashop>adminalsendomoduleconfigurationcontroller_becf4b20f9e9c69b77cd5128f52fdf42'] = 'Minimální časové okno vyzvednutí jsou 2 hodiny';
$_MODULE['<{alsendo}prestashop>adminalsendomoduleconfigurationcontroller_d10fb60c71d2c232120f9df29cbc7989'] = 'Ověření selhalo';
$_MODULE['<{alsendo}prestashop>adminalsendomoduleconfigurationcontroller_0748f75cd6b8aa32b04416c2901d9583'] = 'Výchozí hodiny vyzvednutí úspěšně uloženy';
$_MODULE['<{alsendo}prestashop>adminalsendomoduleconfigurationcontroller_f1da99d03478199bca484cb3c362a51a'] = 'App ID je vyžadováno';
$_MODULE['<{alsendo}prestashop>adminalsendomoduleconfigurationcontroller_2e358d401bc1b53ce208c522405204c3'] = 'App Secret je vyžadováno';
$_MODULE['<{alsendo}prestashop>adminalsendomoduleconfigurationcontroller_09da595ec1651ec189eaa3810b75bd04'] = 'Token je vyžadován';
$_MODULE['<{alsendo}prestashop>adminalsendomoduleconfigurationcontroller_ff10eabd3432cc85a6189d65deb56222'] = 'API klíč je vyžadován';
$_MODULE['<{alsendo}prestashop>adminalsendomoduleconfigurationcontroller_974f271d4641f8dcd92963c1ae57588b'] = 'Název šablony je vyžadován';
$_MODULE['<{alsendo}prestashop>adminalsendomoduleconfigurationcontroller_18ae506456755e4962dde66384d8a48a'] = 'Název šablony musí být jedinečný';
$_MODULE['<{alsendo}prestashop>adminalsendomoduleconfigurationcontroller_c096f5a7351d654cc259204292823929'] = 'Neplatný typ adresy';
$_MODULE['<{alsendo}prestashop>adminalsendomoduleconfigurationcontroller_30ddec8a30741a042414e14506eaa4e6'] = 'Země musí být ve formátu ISO2';
$_MODULE['<{alsendo}prestashop>adminalsendomoduleconfigurationcontroller_75141fb336412517a1fde7d621dc8888'] = 'Telefon musí obsahovat pouze číslice, max 16';
$_MODULE['<{alsendo}prestashop>adminalsendomoduleconfigurationcontroller_681c7d68f4df5cdfbe3e1a29d22c275a'] = 'Neplatný email';
$_MODULE['<{alsendo}prestashop>adminalsendomoduleconfigurationcontroller_18d0a2d5d98b7627c64c55b7b154fc18'] = 'Neplatný typ balíku';
$_MODULE['<{alsendo}prestashop>adminalsendomoduleconfigurationcontroller_c81778e769ffb9a0bbefd6f1ac13df4e'] = 'Šířka musí být větší než 0';
$_MODULE['<{alsendo}prestashop>adminalsendomoduleconfigurationcontroller_8e03818c81e56b1d74ade6b2f1c13bfe'] = 'Délka musí být větší než 0';
$_MODULE['<{alsendo}prestashop>adminalsendomoduleconfigurationcontroller_d7b07d16a1284eba785e41255eba952f'] = 'Výška musí být větší než 0';
$_MODULE['<{alsendo}prestashop>adminalsendomoduleconfigurationcontroller_b0d381a3644899675357497a0f0bf4e5'] = 'Hmotnost musí být větší než 0';
$_MODULE['<{alsendo}prestashop>adminalsendomoduleconfigurationcontroller_ebc00b841688fb2ca7e67a31737a5f14'] = 'Dobírka nemůže být záporná';
$_MODULE['<{alsendo}prestashop>adminalsendomoduleconfigurationcontroller_931f38035933f3ad1b7009253bd94d9b'] = 'Deklarovaná hodnota nemůže být záporná';
$_MODULE['<{alsendo}prestashop>adminalsendomoduleconfigurationcontroller_b206c51ec418678a4ea2869883d890f5'] = 'Neplatný typ vyzvednutí';
$_MODULE['<{alsendo}prestashop>adminalsendomoduleconfigurationcontroller_db8ecb61f23a81a38b2c83858269f951'] = 'Uloženo!';
$_MODULE['<{alsendo}prestashop>adminalsendoshippingmethodscontroller_5634b0239204e864fd2b608103b7841a'] = 'Neplatný formát dat';
$_MODULE['<{alsendo}prestashop>adminalsendoshippingmethodscontroller_db8ecb61f23a81a38b2c83858269f951'] = 'Uloženo!';
$_MODULE['<{alsendo}prestashop>adminalsendoshippingconfigurationcontroller_5992528f236587164ee4f212b9460410'] = 'Neplatná data';
$_MODULE['<{alsendo}prestashop>adminalsendoshippingconfigurationcontroller_db8ecb61f23a81a38b2c83858269f951'] = 'Uloženo!';
$_MODULE['<{alsendo}prestashop>adminalsendobulksendcontroller_b91b5086ca246bba5fda00ab17570238'] = 'Neplatné ID dávky';

// Sender template field validation
$_MODULE['<{alsendo}prestashop>adminalsendomoduleconfigurationcontroller_5ac630477eed50cbc720131ce9208508'] = 'Firma vyžadována, max 50 znaků';
$_MODULE['<{alsendo}prestashop>adminalsendomoduleconfigurationcontroller_0b121f638dea44457b2aca7f9c51376d'] = 'Název firmy příliš dlouhý, max 50 znaků';
$_MODULE['<{alsendo}prestashop>adminalsendomoduleconfigurationcontroller_637491a9bda127fd0e1db82202d76812'] = 'Křestní jméno vyžadováno, max 50 znaků';
$_MODULE['<{alsendo}prestashop>adminalsendomoduleconfigurationcontroller_a0b3b3ac43b29938746b13e1671bc7f2'] = 'Příjmení vyžadováno, max 50 znaků';
$_MODULE['<{alsendo}prestashop>adminalsendomoduleconfigurationcontroller_446f3519335f1ab42e2123529e8d8dc8'] = 'Ulice vyžadována, max 50 znaků';
$_MODULE['<{alsendo}prestashop>adminalsendomoduleconfigurationcontroller_3225acbc3b4327a54960286257f62319'] = 'Číslo popisné vyžadováno, max 10 znaků';
$_MODULE['<{alsendo}prestashop>adminalsendomoduleconfigurationcontroller_5f3fb40e023ba2fa92db139824a1cf52'] = 'Číslo bytu příliš dlouhé';
$_MODULE['<{alsendo}prestashop>adminalsendomoduleconfigurationcontroller_a748eb2780a2c3ba10b478318e269f73'] = 'PSČ vyžadováno, max 50 znaků';
$_MODULE['<{alsendo}prestashop>adminalsendomoduleconfigurationcontroller_5542c9eeeba50c0b7f8bc3c43f14f7e6'] = 'Město vyžadováno, max 50 znaků';
$_MODULE['<{alsendo}prestashop>adminalsendomoduleconfigurationcontroller_dc5c72842bf3c4fcdf506fd4dd8a26a4'] = 'Kontaktní osoba vyžadována, max 50 znaků';
$_MODULE['<{alsendo}prestashop>adminalsendomoduleconfigurationcontroller_a22aa19364683c9fb4d6bac9f4505ba4'] = 'Nastaveno jako výchozí!';
$_MODULE['<{alsendo}prestashop>adminalsendomoduleconfigurationcontroller_e0626222614bdee31951d84c64e5e9ff'] = 'Vybrat';
$_MODULE['<{alsendo}prestashop>adminalsendomoduleconfigurationcontroller_1063e38cb53d94d386f21227fcd84717'] = 'Odstranit';
$_MODULE['<{alsendo}prestashop>adminalsendomoduleconfigurationcontroller_dd648d8301d047eafb7e7f9e4e5fca77'] = 'Client ID a Client Secret Ecolet jsou vyžadovány. Nejprve je uložte.';
$_MODULE['<{alsendo}prestashop>adminalsendomoduleconfigurationcontroller_87fa9a9d067068c441eb1cfffa81f82f'] = 'Client ID a Client Secret jsou vyžadovány pro Rumunsko';

// AdminAlsendoOrderController — validation error translations
$_MODULE['<{alsendo}prestashop>adminalsendoordercontroller_0c0032f6c60b43f1755dc20473766fee'] = 'Křestní jméno příjemce je povinné';
$_MODULE['<{alsendo}prestashop>adminalsendoordercontroller_0a5d9c718bac854f60cc46c621843471'] = 'Příjmení příjemce je povinné';
$_MODULE['<{alsendo}prestashop>adminalsendoordercontroller_5f831d656cd5bd110ad2bdff711f7dab'] = 'Ulice příjemce je povinná';
$_MODULE['<{alsendo}prestashop>adminalsendoordercontroller_942c77f34c984dc3f3fdb06adafae900'] = 'Číslo domu příjemce je povinné';
$_MODULE['<{alsendo}prestashop>adminalsendoordercontroller_ce9550c8d37d35fa78c070d34664cb9b'] = 'PSČ příjemce je povinné';
$_MODULE['<{alsendo}prestashop>adminalsendoordercontroller_453f63d4c8b4c53becb19548c61fd03b'] = 'Město příjemce je povinné';
$_MODULE['<{alsendo}prestashop>adminalsendoordercontroller_c55750774188301c1f77a5dbb3bd7af8'] = 'Platný kód země příjemce je povinný';
$_MODULE['<{alsendo}prestashop>adminalsendoordercontroller_518fa518e5a2aec1cdc3abb6e545b2eb'] = 'Telefonní číslo příjemce je povinné';
$_MODULE['<{alsendo}prestashop>adminalsendoordercontroller_335578741becb5d354a0f243d5ce640a'] = 'Platný e-mail příjemce je povinný';
$_MODULE['<{alsendo}prestashop>adminalsendoordercontroller_a6cecf27950e0513b87912de2d29ea5c'] = 'Jméno odesílatele je povinné';
$_MODULE['<{alsendo}prestashop>adminalsendoordercontroller_6dd98d23c4ff95428b2b970c91da0193'] = 'Ulice odesílatele je povinná';
$_MODULE['<{alsendo}prestashop>adminalsendoordercontroller_b4279bce48e04d2e0011c72589fe1d91'] = 'PSČ odesílatele je povinné';
$_MODULE['<{alsendo}prestashop>adminalsendoordercontroller_8c079a799fd64d14eaf0ab7190b049bf'] = 'Město odesílatele je povinné';
$_MODULE['<{alsendo}prestashop>adminalsendoordercontroller_a1c2a3c1754b9debdc07c7048e885903'] = 'Platný kód země odesílatele je povinný';
$_MODULE['<{alsendo}prestashop>adminalsendoordercontroller_96413cb1722b49b4e42ed4847123639f'] = 'Platné telefonní číslo odesílatele je povinné';
$_MODULE['<{alsendo}prestashop>adminalsendoordercontroller_c18a7032240b262bbc819a041e50b948'] = 'Platný e-mail odesílatele je povinný';
$_MODULE['<{alsendo}prestashop>adminalsendoordercontroller_20ea196c8e18043eec6a26c3b04fb21a'] = 'Číslo bankovního účtu je povinné pro dobírku';
$_MODULE['<{alsendo}prestashop>adminalsendoordercontroller_7eb978d8047f7bf042615469f4a395d4'] = 'Neplatný formát IBAN';
$_MODULE['<{alsendo}prestashop>adminalsendoordercontroller_b320c24c6a1cf2b36edce6c977f83e2a'] = 'Šířka balíku musí být větší než 0';
$_MODULE['<{alsendo}prestashop>adminalsendoordercontroller_82cf59170dbf8edaee376779b4b528a2'] = 'Délka balíku musí být větší než 0';
$_MODULE['<{alsendo}prestashop>adminalsendoordercontroller_aa3306f90c21142397c8cbe0819795de'] = 'Výška balíku musí být větší než 0';
$_MODULE['<{alsendo}prestashop>adminalsendoordercontroller_f2afbdb138aff882798bc1b503c2ad0a'] = 'Hmotnost balíku musí být větší než 0';
$_MODULE['<{alsendo}prestashop>adminalsendoordercontroller_50f197142a91a42cdcd7ee680018960b'] = 'Přepravní služba musí být vybrána';
$_MODULE['<{alsendo}prestashop>adminalsendoordercontroller_69a00ce0e14573388aa198426e080777'] = 'Výdejní místo musí být vybráno';
$_MODULE['<{alsendo}prestashop>adminalsendoordercontroller_ea65c259a490d77909984f6e200e7166'] = 'Datum vyzvednutí je povinné pro kurýrní svoz';
$_MODULE['<{alsendo}prestashop>adminalsendoordercontroller_2a9eb49d3ef2c9f488ebd4cc4e1d98ee'] = 'Datum vyzvednutí nemůže být v minulosti. Vyberte dnešní nebo budoucí datum.';
$_MODULE['<{alsendo}prestashop>adminalsendoordercontroller_3c188774ccf2910f6585941d7d315b9a'] = 'Čas začátku vyzvednutí je povinný pro kurýrní svoz';
$_MODULE['<{alsendo}prestashop>adminalsendoordercontroller_201b3858f68d3edc500a32b6595c295d'] = 'Čas konce vyzvednutí je povinný pro kurýrní svoz';
$_MODULE['<{alsendo}prestashop>adminalsendoordercontroller_ecfdbdce910e39dc1e71c679a8b0c2bf'] = 'Čas vyzvednutí nemůže být dříve než 08:00';
$_MODULE['<{alsendo}prestashop>adminalsendoordercontroller_aab141da780d8d4e50915d1908575133'] = 'Čas vyzvednutí nemůže být dříve než 09:00';
$_MODULE['<{alsendo}prestashop>adminalsendoordercontroller_130725383c99e854d3014f3b324892bd'] = 'Čas vyzvednutí nemůže být později než 17:00 kvůli provozním hodinám kurýra';
$_MODULE['<{alsendo}prestashop>adminalsendoordercontroller_b05266d1ab43fed608c145486b323e1a'] = 'Čas konce vyzvednutí musí být po čase začátku';
$_MODULE['<{alsendo}prestashop>adminalsendoordercontroller_b428d06830a85c77bb92d60d9ee289cf'] = 'Minimální časové okno vyzvednutí je 2 hodiny';
$_MODULE['<{alsendo}prestashop>adminalsendoordercontroller_8cb363cb3e27206438c54d57a3c522fc'] = 'Čas konce vyzvednutí již uplynul. Vyberte pozdější čas nebo jiný den.';
$_MODULE['<{alsendo}prestashop>adminalsendoordercontroller_b205b164fd9705eec609e7c6589f163a'] = 'Přepravce odmítl objednávku vyzvednutí — nakonfigurované hodiny vyzvednutí jsou mimo rozvrh přepravce pro tuto službu. Upravte výchozí hodiny vyzvednutí v nastavení modulu (Nastavení → Výchozí hodiny vyzvednutí) nebo použijte úplný formulář pro nastavení pozdějšího času vyzvednutí.';

// order_full_form.tpl — JS message translations
$_MODULE['<{alsendo}prestashop>order_full_form_d13064b91e14cb8b990b58d5ed6609c8'] = 'Chyby validace. Opravte pole označená červeně.';
$_MODULE['<{alsendo}prestashop>order_full_form_fe84eefb7e61c02ae72cef8e6c9a35a5'] = 'Chyba při získávání cenové nabídky';
$_MODULE['<{alsendo}prestashop>order_full_form_fb34a2f7e388aa01c95685a1568ee26e'] = 'Požadavek na cenovou nabídku selhal:';
$_MODULE['<{alsendo}prestashop>order_full_form_8cb363cb3e27206438c54d57a3c522fc'] = 'Čas konce vyzvednutí již uplynul. Vyberte pozdější čas nebo jiný den.';
$_MODULE['<{alsendo}prestashop>order_full_form_2a9eb49d3ef2c9f488ebd4cc4e1d98ee'] = 'Datum vyzvednutí nemůže být v minulosti. Vyberte dnešní nebo budoucí datum.';
$_MODULE['<{alsendo}prestashop>order_full_form_c38a081c1f3770d8059ffcd2cfd6677c'] = 'Zásilka byla úspěšně vytvořena. Přesměrování...';
$_MODULE['<{alsendo}prestashop>order_full_form_a9fcc389dc8de344977d86cb6044cad2'] = 'Požadavek selhal:';
$_MODULE['<{alsendo}prestashop>order_full_form_2bb6b30dcb7c6c214a59dd0a4d5f4926'] = 'Podrobnosti objednávky úspěšně uloženy.';
$_MODULE['<{alsendo}prestashop>order_full_form_855d1a98e48710725476db3803492cd5'] = 'Chyba ukládání:';
$_MODULE['<{alsendo}prestashop>order_full_form_1e82a3c10f91e5aed80fbd1cfe696494'] = 'Odeslat tuto objednávku s výchozím nastavením?';
$_MODULE['<{alsendo}prestashop>order_full_form_a82c5618f8ca911ae11a7fc2fc8e43e9'] = 'Zásilka úspěšně odeslána!';
$_MODULE['<{alsendo}prestashop>order_full_form_588eebd0d78aa9998fa3376428396532'] = 'Rychlé odeslání selhalo:';
$_MODULE['<{alsendo}prestashop>order_full_form_b3000a9da27a3cb375ac85c9a91de816'] = 'Zrušit tuto zásilku?';
$_MODULE['<{alsendo}prestashop>order_full_form_fb6a1e5f6cbe1304c73d034d41588397'] = 'Zrušení selhalo';
$_MODULE['<{alsendo}prestashop>order_full_form_23880851d04c4d1ed9c0040514c5675b'] = 'Pro tuto konfiguraci nejsou k dispozici žádné služby.';
$_MODULE['<{alsendo}prestashop>order_full_form_7b0fee4d957f4ffc0ed5abdd184062b7'] = 'Odesílání...';
$_MODULE['<{alsendo}prestashop>order_full_form_6d930d778eeee93d613302399d9cca93'] = 'Čas konce vyzvednutí již uplynul';
$_MODULE['<{alsendo}prestashop>order_full_form_81401a9b7997c5f660a13af66cd31550'] = 'Datum nemůže být v minulosti';
$_MODULE['<{alsendo}prestashop>order_full_form_78fe23ef2795c228e97ffa3064ae356e'] = 'Podrobné chyby:';

// alsendo.php — Media::addJsDef translations
$_MODULE['<{alsendo}prestashop>alsendo_17af645d28a1f1558bc1da88699a3de0'] = 'Bezpečnostní token nenalezen. Obnovte stránku a zkuste to znovu.';
$_MODULE['<{alsendo}prestashop>alsendo_444f487949761e5deef324f121fb8295'] = 'Odeslat přes Alsendo';
$_MODULE['<{alsendo}prestashop>alsendo_a5bae8296b9c2ae54102f89596d3676c'] = 'objednávek vybráno';
$_MODULE['<{alsendo}prestashop>alsendo_a1c3470a944b9625cfb924fd15c8bdbf'] = 'Vyberte alespoň jednu objednávku';

// Zaslat CZ — order_full_form.tpl
$_MODULE['<{alsendo}prestashop>order_full_form_c9b48112685588ebaf8539fac88cddf8'] = 'Kód banky';
$_MODULE['<{alsendo}prestashop>order_full_form_796193be4465a6c95916f897c6cf6822'] = 'IBAN číslo účtu';
$_MODULE['<{alsendo}prestashop>order_full_form_1503f521da7d679bf126821705e65ddb'] = 'Odeslat přes výdejní místo';
$_MODULE['<{alsendo}prestashop>order_full_form_ca0eab7e9e18f2f3f0ed26e2db05f447'] = 'Odevzdejte balík na pobočce místo vyzvednutí kurýrem';
$_MODULE['<{alsendo}prestashop>order_full_form_6bee9ce3ce34434efc039548031047d1'] = 'Požadovat vyzvednutí kurýrem';

// Zaslat CZ — module_configuration.tpl
$_MODULE['<{alsendo}prestashop>module_configuration_1872eb44bb8c134c796d8ddc09fdf5f7'] = 'Adresa z adresáře';
$_MODULE['<{alsendo}prestashop>module_configuration_0c7b0b4de4844f52a5e2539d5bf3e379'] = 'Vyberte adresu...';
$_MODULE['<{alsendo}prestashop>module_configuration_c9b48112685588ebaf8539fac88cddf8'] = 'Kód banky';
$_MODULE['<{alsendo}prestashop>module_configuration_796193be4465a6c95916f897c6cf6822'] = 'IBAN číslo účtu';
$_MODULE['<{alsendo}prestashop>module_configuration_8baa508c34450ffa61320110ffac05a6'] = 'Štítek od kurýra';
$_MODULE['<{alsendo}prestashop>module_configuration_428246aa11abbb4c1147ecd2f0a69db7'] = 'Vlastní štítek (podání na pobočce)';

// module_configuration.tpl — Pickup Date section
$_MODULE['<{alsendo}prestashop>module_configuration_6a654eb684215acbcff90197922ca294'] = 'Datum vyzvednutí';
$_MODULE['<{alsendo}prestashop>module_configuration_abc8f2e026a0c0d91a12ba4894183eb8'] = 'Povolit vyzvednutí ve stejný den (odeslat dnes)';
$_MODULE['<{alsendo}prestashop>module_configuration_5805e9fc4cb10d4c8a5daf34c3f2799e'] = 'Pokud je povoleno: datum vyzvednutí se nastaví na dnešek. Pokud přepravce odmítne vyzvednutí ve stejný den, automaticky se pokusí o zítřek.';
$_MODULE['<{alsendo}prestashop>module_configuration_947441dfa49e19d90511376d94d78cf9'] = 'Pokud je zakázáno: datum vyzvednutí se nastaví na zítřek (další pracovní den).';

return $_MODULE;
