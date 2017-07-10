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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
?>
<form class="form-horizontal">
	<fieldset>
   <div class="form-group">
     <label class="col-lg-3 control-label">{{Apikey firebase/gcm}}</label>
     <div class="col-lg-4">
       <input class="configKey form-control" data-l1key="apiKey" />
     </div>
   </div>
   <div class="form-group">
     <label class="col-lg-3 control-label">{{authDomain firebase/gcm}}</label>
     <div class="col-lg-4">
       <input class="configKey form-control" data-l1key="authDomain" />
     </div>
   </div>
   <div class="form-group">
     <label class="col-lg-3 control-label">{{projectId firebase/gcm}}</label>
     <div class="col-lg-4">
       <input class="configKey form-control" data-l1key="projectId" />
     </div>
   </div>
   <div class="form-group">
     <label class="col-lg-3 control-label">{{messagingSenderId firebase/gcm}}</label>
     <div class="col-lg-4">
       <input class="configKey form-control" data-l1key="messagingSenderId" />
     </div>
   </div>
    <div class="form-group">
     <label class="col-lg-3 control-label">{{Clef serveur}}</label>
     <div class="col-lg-9">
       <textarea class="configKey form-control" data-l1key="serverKey" ></textarea>
     </div>
   </div>
 </fieldset>
</form>

<script>
  function gcm_postSaveConfiguration(){
   $.ajax({
    type: "POST",
    url: "plugins/gcm/core/ajax/gcm.ajax.php",
    data: {
      action: "genFirebaseMsgTmpl",
    },
    dataType: 'json',
    error: function (request, status, error) {
      handleAjaxError(request, status, error);
    },
    success: function (data) {
      if (data.state != 'ok') {
        $('#div_alert').showAlert({message: data.result, level: 'danger'});
        return;
      }
    }
  });
 }

</script>