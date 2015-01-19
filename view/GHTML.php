<?php
/*
 * Autor Bertram Buchardt
 * Alle Rechte vorbehalten
 * 
 */
require_once ROOT . '/model/entity/Status.php';

/**
 * Description of GHTML
 *
 * @author Bertram
 */
class GHTML {

    public static function getStatusOptions($status = '') {
        ob_start();
        ?>

        <option value="<?= STATUS::NEU ?>"<?= ($status === STATUS::NEU) ? 'selected="selected"' : '' ?>><?= STATUS::NEU ?></option>
        <option value="<?= STATUS::AKTIV ?>"<?= ($status === STATUS::AKTIV) ? 'selected="selected"' : '' ?>><?= STATUS::AKTIV ?></option>
        <option value="<?= STATUS::EINGEREICHT ?>"<?= ($status === STATUS::EINGEREICHT) ? 'selected="selected"' : '' ?>><?= STATUS::EINGEREICHT ?></option>
        <option value="<?= STATUS::DEAKTIV ?>"<?= ($status === STATUS::DEAKTIV) ? 'selected="selected"' : '' ?>><?= STATUS::DEAKTIV ?></option>

        <?php
        return ob_get_clean();
    }

}
