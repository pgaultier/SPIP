<?php

/***************************************************************************\
 *  SPIP, Systeme de publication pour l'internet                           *
 *                                                                         *
 *  Copyright (c) 2001-2008                                                *
 *  Arnaud Martin, Antoine Pitrou, Philippe Riviere, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('action/supprimer');

// http://doc.spip.org/@action_documenter_dist
function action_documenter_dist()
{
	$securiser_action = charger_fonction('securiser_action', 'inc');
	$arg = $securiser_action();

	if (!preg_match(",^(-?)(\d+)\W(\w+)\W?(\d*)\W?(\d*)$,", $arg, $r))
		spip_log("action_documenter $arg pas compris");
	else action_documenter_post($r);
}

// http://doc.spip.org/@action_documenter_post
function action_documenter_post($r)
{
  list($x, $sign, $id, $type, $vignette, $suite) = $r;

	if ($vignette) {
		// on ne supprime pas, on dissocie
		// supprimer_document_et_vignette($vignette);
		// on dissocie, mais si le doc est utilise dans le texte, il sera reassocie ..., donc condition sur vu !
		sql_delete("spip_documents_liens",
			"id_objet=".intval($id)."objet=".sql_quote($type)." AND id_document=".sql_quote($vignette)." AND (vu='non' OR vu IS NULL)");
		// Cas de destruction de la vignette seulement
		if ($suite)
			sql_updateq("spip_documents", array('id_vignette' => 0), "id_document=$suite");

		// Ensuite on supprime les docs orphelins, ca supprimera
		// physiquement notre document s'il n'est pas attache ailleurs
		// Je mets l'option a *false* pour ne rien casser chez les
		// experimentateurs [FORMULAIRE_UPLOAD, FORMS&TABLES], mais
		// par defaut ca devrait etre *true*
		// Quoi qu'il en soit les boucles n'affichent plus les documents
		// orphelins, sauf critere {tout}
		define('_SUPPRIMER_DOCUMENTS_ORPHELINS', false);
		if (_SUPPRIMER_DOCUMENTS_ORPHELINS) {
			include_spip('inc/documents');
			supprimer_les_documents_orphelins();
		}
		// Version plus soft : on ne supprime que le doc en cours de suppression
		include_spip('inc/documents');
		if (in_array($vignette, lister_les_documents_orphelins()))
			supprimer_documents(array($vignette));
	}
	else {
		if ($sign)
			$x = sql_select("docs.id_document", "spip_documents AS docs, spip_documents_liens AS l", "l.id_objet=".intval($id)." AND l.objet=".sql_quote($type)." AND l.id_document=docs.id_document AND docs.mode='document' AND docs.extension IN ('gif', 'jpg', 'png')");
		else
			$x = sql_select("docs.id_document", "spip_documents AS docs, spip_documents_liens AS l", "l.id_objet=".intval($id)." AND l.objet=".sql_quote($type)." AND l.id_document=docs.id_document AND docs.mode='document'  AND docs.extension NOT IN ('gif', 'jpg', 'png')");

		while ($r = sql_fetch($x)) {
			// supprimer_document_et_vignette($r['id_document']);
			// on dissocie, mais si le doc est utilise dans le texte,
			// il sera reassocie ..., donc condition sur vu !
			sql_delete("spip_documents_liens", "id_objet=".intval($id)." AND objet=".sql_quote($type)."  AND id_document=".$r['id_document']." AND (vu='non' OR vu IS NULL)");
		}
	}
	if ($type == 'rubrique') {
		include_spip('inc/rubriques');
		depublier_branche_rubrique_if($id);
	}
}
?>
