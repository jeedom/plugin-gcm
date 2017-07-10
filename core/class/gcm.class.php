<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
require_once dirname(__FILE__) . '/../../vendor/autoload.php';

class gcm extends eqLogic {
	/*     * *************************Attributs****************************** */

	/*     * ***********************Methode static*************************** */

	public static function checkAndCreate($_id) {
		$eqLogic = self::byLogicalId(substr($_id, 0, 127), 'gcm');
		if (is_object($eqLogic)) {
			return;
		}
		$eqLogic = new self();
		$eqLogic->setLogicalId(substr($_id, 0, 127));
		$eqLogic->setName($_id);
		$eqLogic->setConfiguration('id', $_id);
		$eqLogic->setEqType_name('gcm');
		$eqLogic->setIsVisible(0);
		$eqLogic->setIsEnable(1);
		$eqLogic->save();
	}

	public static function genFirebaseMsgTmpl() {
		if (file_exists(dirname(__FILE__) . '/../js/firebase-messaging-sw.js')) {
			unlink(dirname(__FILE__) . '/../js/firebase-messaging-sw.js');
		}
		$content = file_get_contents(dirname(__FILE__) . '/../js/firebase-messaging-sw-tmpl.js');
		$replace = array(
			'#messagingSenderId#' => config::bykey('messagingSenderId', 'gcm'),
		);
		file_put_contents(dirname(__FILE__) . '/../js/firebase-messaging-sw.js', str_replace(array_keys($replace), $replace, $content));
	}

	public static function send($_datas) {
		$headers = array(
			'Authorization: key=' . config::byKey('serverKey', 'gcm'),
			'Content-Type: application/json',
		);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($_datas));
		$result = curl_exec($ch);
		curl_close($ch);
		if (!is_json($result)) {
			throw new Exception(__('Erreur lors de l\'envoi du message : ', __FILE__) . $result . __(' pour la requete : ', __FILE__) . json_encode($_datas));
		}
		$return = json_decode($result, true);
		if ($return['failure'] != 0) {
			throw new Exception(__('Erreur lors de l\'envoi du message : ', __FILE__) . $result . __(' pour la requete : ', __FILE__) . json_encode($_datas));
		}
		return $return;

	}

	/*     * *********************Méthodes d'instance************************* */

	public function postSave() {
		$notify = $this->getCmd(null, 'notify');
		if (!is_object($notify)) {
			$notify = new gcmCmd();
			$notify->setLogicalId('notify');
			$notify->setName(__('Notifier', __FILE__));
		}
		$notify->setType('action');
		$notify->setSubType('message');
		$notify->setEqLogic_id($this->getId());
		$notify->save();

		if ($this->getLogicalId() != 'all') {
			$eqLogic = self::byLogicalId('all', 'gcm');
			if (is_object($eqLogic)) {
				return;
			}
			$eqLogic = new self();
			$eqLogic->setLogicalId('all');
			$eqLogic->setName('Tous les GCMs');
			$eqLogic->setConfiguration('id', 'all');
			$eqLogic->setEqType_name('gcm');
			$eqLogic->setIsVisible(0);
			$eqLogic->setIsEnable(1);
			$eqLogic->save();
		}
	}

	/*     * **********************Getteur Setteur*************************** */
}

class gcmCmd extends cmd {
	/*     * *************************Attributs****************************** */

	/*     * ***********************Methode static*************************** */

	/*     * *********************Methode d'instance************************* */

	public function execute($_options = null) {
		if ($this->getType() == 'info') {
			return;
		}
		$eqLogic = $this->getEqLogic();
		$data = array(
			'priority' => 'high',
			'notification' => array(
				'title' => $_options['title'],
				'body' => $_options['message'],
			),
		);
		if ($eqLogic->getConfiguration('id') == 'all') {
			$data['registration_ids'] = array();
			foreach (gcm::byType('gcm') as $gcm) {
				if ($gcm->getConfiguration('id') == 'all') {
					continue;
				}
				$data['registration_ids'][] = $gcm->getConfiguration('id');
			}
		} else {
			$data['to'] = $eqLogic->getConfiguration('id');
		}
		gcm::send($data);
	}

	/*     * **********************Getteur Setteur*************************** */
}

?>
