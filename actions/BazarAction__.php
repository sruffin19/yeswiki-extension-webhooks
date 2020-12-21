<?php

use YesWiki\Bazar\Service\FicheManager;
use YesWiki\Core\Service\TripleStore;
use YesWiki\Core\YesWikiAction;

class BazarAction__ extends YesWikiAction
{
    function run()
    {
        $ficheManager = $this->getService(FicheManager::class);
        $tripleStore = $this->getService(TripleStore::class);

        $GLOBALS['params'] = getAllParameters($this->wiki);

        $view = $GLOBALS['params'][BazarAction::VARIABLE_VOIR];
        $action = $GLOBALS['params'][BazarAction::VARIABLE_ACTION];

        switch ($view) {
            // Display webhooks form before the forms list
            case BazarAction::VOIR_FORMULAIRE:
                if( !isset($_GET['action_formulaire']) ) {
                    return webhooks_formulaire();
                }
                break;

            // Call webhook on addition
            case BazarAction::VOIR_CONSULTER:
                switch($action) {
                    case BazarAction::VOIR_FICHE:
                        if( $_GET['message']==='ajout_ok' ) {
                            // We set this condition because otherwise the page is called twice and the webhook is sent twice
                            // TODO: Understand why the YesWiki core calls this kind of page twice
                            if( !isset($GLOBALS['add_webhook_already_called']) ) {
                                $fiche = $ficheManager->getOne($_GET['id_fiche']);
                                webhooks_post_all($fiche, WEBHOOKS_ACTION_ADD);
                                $GLOBALS['add_webhook_already_called'] = true;
                            }
                        }
                }
                break;

            // Incoming webhook for tests
            case WEBHOOKS_VUE_TEST:
                $tripleStore->create(
                    $this->wiki->GetPageTag(),
                    WEBHOOKS_VOCABULARY_TEST,
                    file_get_contents('php://input'),
                    '',
                    ''
                );
                break;
        }

    }
}