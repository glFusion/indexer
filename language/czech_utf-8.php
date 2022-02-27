<?php
/**
* glFusion CMS
*
* Indexer - Indexer Plugin for glFusion
*
* Language File - UTF-8
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on the Searcher Plugin for glFusion
*  @copyright  Copyright (c) 2017-2018 Lee Garner - lee AT leegarner DOT com
*
*/

if (!defined('GVERSION')) {
    die('This file can not be used on its own.');
}

$LANG_INDEXER = array(
    'menu_label'    => 'Rejstřík',
    'all'           => 'Vše',
    'all_posts_by'  => 'Všechny příspěvky od ',
    'by'            => 'od',
    'hits'          => 'Hitů',
    'new_search'    => 'Zadejte výše uvedená kritéria vyhledávání a vyberte Hledat',
    'on'            => 'zapnuto',
    'one_day'       => '1 den',
    'one_month'     => '1 měsíc',
    'one_year'      => '1 rok',
    'search'        => 'Hledej',
    'seven_days'    => '7 dní',
    'showing_results' => 'Zobrazeno %s až %s z %s výsledů',
    'six_months'    => '6 měsíců',
    'three_months'  => '3 měsíce',
    'two_weeks'     => '2 týdny',
    'two_years'     => '2 roky',
    'seconds'       => 'sekund',
    'search_placeholder' => 'Zadejte Váš požadavek na hledání',
);

$LANG_INDEXER_ADM = array(
    'cancel'            => 'Zrušit',
    'chk_unchk_all'     => 'Zaškrtnout/Odznačit vše',
    'clear_counters' => 'Vymazat počítadlo',
    'content_type'      => 'Typ obsahu',
    'current_progress'  => 'Současný průběh',
    'empty_table'       => 'Odstranit všechna data z indexovací tabulky před indexováním',
    'error_getcontenttypes' => 'Nelze načíst typy obsahu z glFusion',
    'error_heading'     => 'Chyby',
    'hlp_counters'      => 'Zde jsou vyhledávací dotazy vytvořené návštěvníky stránek, spolu s počtem toho, kolikrát byl každý dotaz proveden.',
    'hlp_gen_all'       => 'Znovu vygenerovat všechny indexy pro vybrané typy obsahu. Použijte tuto možnost po instalaci pluginu nebo po změně některých klíčových konfiguračních položek, jako je minimální délka slova nebo změna vyhledávání.',
    'hlp_reindex'       => 'Přeindexování obsahu odstraní všechny existující položky vyhledávání pro typ obsahu a znovu prohlédné obsah pro opětovné sestavení indexu hledaných slov. To může u typů velkého objemu obsahu, například u fóra, trvat značně dlouho.',
    'index_instructions'=> 'Toto prohledá obsah a znovu sestaví index vyhledávání',
    'index_status'      => 'Stav indexování',
    'indexer_admin'     => 'Správce indexování',
    'indexing'          => 'Indexování',
    'no_errors'         => 'Bez chyby',
    'overall_progress'  => 'Celkový průběh',
    'queries'           => 'Dotazy',
    'reindex_button'    => 'Znovu indexovat',
    'reindex_title'     => 'Znovu indexovat obsah',
    'remove_content_1'  => 'Odstranění stávajících indexových položek pro ',
    'remove_content_2'  => ' To může trvat i několik minut....',
    'remove_fail'       => 'Nepodařilo se odstranit existující položky indexu.',
    'results'           => 'Výsledky',
    'retrieve_content_list' => 'Načítání seznamu obsahu pro ',
    'retrieve_content_types'=> 'Načítání typů obsahu',
    'search_terms'  => 'Hledané výrazy',
    'submit'            => 'Potvrdit',
    'success'           => 'Úspěch',
);

$LANG_configsections['indexer'] = array(
    'label' => 'Rejstřík',
    'title' => 'Konfigurace pluginu indexování',
);

$LANG_confignames['indexer'] = array(
    'excerpt_length'        => 'Velikost výpisu',
    'per_page'              => 'Výsledků na stránku',
    'replace_stock_search'  => 'Nahradit základní glFusion vyhledávání?',
    'search_type'           => 'Výchozí metoda vyhledávání',
    'stopwords'             => 'Stopslova',
    'summarize_discussions' => 'Indexovat příspěvky na fóru podle nadřazených příspěvků',
);

$LANG_configsubgroups['indexer'] = array(
    'sg_main' => 'Nastavení',
);

$LANG_fs['indexer'] = array(
    'fs_main' => 'Nastavení',
);

$LANG_configSelect['indexer'] = array(
    0  => array(1 => 'Ano', 0 => 'Ne'),
    1  => array('all' => 'Všechna tato slova', 'any' => 'Jakékoli z těchto slov', 'phrase' => 'Přesná fráze'),
);

?>
