<?php

include ("ecrire/inc_version.php3");
$dir_ecrire = 'ecrire/';
include_local ("inc-cache.php3");


//
// Inclusions de squelettes
//

function inclure_fichier($fond, $delais, $contexte_inclus = "") {
	$fichier_requete = $fond;
	if (is_array($contexte_inclus)) {
		reset($contexte_inclus);
		while(list($key, $val) = each($contexte_inclus)) $fichier_requete .= '&'.$key.'='.$val;
	}
	$fichier_cache = generer_nom_fichier_cache($fichier_requete);
	$chemin_cache = "CACHE/$fichier_cache";

	$use_cache = utiliser_cache($chemin_cache, $delais);

	if (!$use_cache) {
		include_local("inc-calcul.php3");
		$fond = chercher_squelette($fond, $contexte_inclus['id_rubrique']);
		$page = calculer_page($fond, $contexte_inclus);
		if ($page) {
			spip_log("calcul($delais): $chemin_cache");
			ecrire_fichier_cache($chemin_cache, $page);
		}
	}
	return $chemin_cache;
}


//
// Gestion du cache et calcul de la page
//

$fichier_requete = $REQUEST_URI;
$fichier_requete = strtr($fichier_requete, '?', '&');
$fichier_requete = eregi_replace('&(submit|valider|(var_[^=&]*)|recalcul)=[^&]*', '', $fichier_requete);

$fichier_cache = generer_nom_fichier_cache($fichier_requete);
$chemin_cache = "CACHE/$fichier_cache";

$use_cache = utiliser_cache($chemin_cache, $delais);

if (!$use_cache OR !defined("_ECRIRE_INC_META_CACHE")) {
	include_ecrire("inc_meta.php3");
}


//
// Authentification
//

if ($HTTP_COOKIE_VARS['spip_session'] OR $PHP_AUTH_USER) {
	include_ecrire ("inc_session.php3");
	verifier_visiteur();
}

//
// Ajouter un forum
//

if ($ajout_forum) {
	include_local ("inc-forum.php3");
	ajout_forum();
}


// date relative a l'envoi du mail nouveautes
$GLOBALS['date_nouv'] = date('Y-m-d H:i:s', lire_meta('majnouv'));

if (!$use_cache) {
	$lastmodified = time();
	if (($lastmodified - lire_meta('date_purge_cache')) > 24 * 3600) {
		ecrire_meta('date_purge_cache', $lastmodified);
		$f = fopen('CACHE/.purge', 'w');
		fclose($f);
	}

	//
	// Recalculer le cache
	//

	$calculer_cache = true;

	// redirection d'article via le chapo =http...
	if ($id_article = intval($id_article)) {
		$query = "SELECT chapo FROM spip_articles WHERE id_article='$id_article'";
		$result = spip_query($query);
		while($row = spip_fetch_array($result)) {
			$chapo = $row['chapo'];
		}
		if (substr($chapo, 0, 1) == '=') {
			include_ecrire('inc_texte.php3');

			$regs = array('','','',substr($chapo, 1));
			list(,$url) = extraire_lien($regs);
			$url = addslashes($url);
			$texte = "<"."?php @header (\"Location: $url\"); ?".">";
			$calculer_cache = false;
			spip_log("redirection: $url");
			ecrire_fichier_cache($chemin_cache, $texte);
		}
	}

	if ($calculer_cache) {
		include_local ("inc-calcul.php3");
		$page = calculer_page_globale($fond);
		if ($page) {
			spip_log("calcul($delais): $chemin_cache");
			ecrire_fichier_cache($chemin_cache, $page);
		}
	}
}


//
// si $var_recherche est positionnee, on met en rouge les mots cherches (php4 uniquement)
//

if ($var_recherche AND $flag_ob AND $flag_pcre AND !$flag_preserver AND !$mode_surligne) {
	include_ecrire("inc_surligne.php3");
	$mode_surligne = 'auto';
	ob_start("");
} else {
	unset ($var_recherche);
	unset ($mode_surligne);
}

//
// Inclusion du cache pour envoyer la page au client
//

$effacer_cache = !$delais; // $delais peut etre modifie par une inclusion de squelette...

if (!$effacer_cache && !$flag_dynamique && $recalcul != 'oui') {
	if ($lastmodified) {
		@Header ("Last-Modified: ".gmdate("D, d M Y H:i:s", $lastmodified)." GMT");
		@Header ("Expires: ".gmdate("D, d M Y H:i:s", $lastmodified + $delais)." GMT");
	}
}
else {
	@Header("Expires: 0");
	@Header("Cache-Control: no-cache,must-revalidate");
	@Header("Pragma: no-cache");
}

if (file_exists($chemin_cache) && ($HTTP_SERVER_VARS['REQUEST_METHOD'] != 'HEAD')) {
	include ($chemin_cache);
}


//
// suite et fin mots en rouge
//

if ($var_recherche) {
	fin_surligne($var_recherche, $mode_surligne);
	$timeout = true; // risque timeout
}


//
// nettoie
//

if ($effacer_cache) @unlink($chemin_cache);


//
// Verifier la presence du .htaccess dans le cache, sinon le generer
//

if (!file_exists("CACHE/.htaccess")) {
	if ($hebergeur == 'nexenservices'){
		echo "<font color=\"#FF0000\">IMPORTANT : </font>";
		echo "Votre h&eacute;bergeur est Nexen Services.<br />";
		echo "La protection du r&eacute;pertoire <i>CACHE/</i> doit se faire par l'interm&eacute;diaire de ";
		echo "<a href=\"http://www.nexenservices.com/webmestres/htlocal.php\" target=\"_blank\">l'espace webmestres</a>.";
		echo "Veuillez cr&eacute;er manuellement la protection pour ce r&eacute;pertoire (un couple login/mot de passe est n&eacute;cessaire).<br />";
	}
	else{
		$f = fopen("CACHE/.htaccess", "w");
		fputs($f, "deny from all\n");
		fclose($f);
	}
}



//
// Fonctionnalites administrateur (declenchees par le cookie admin, authentifie ou non)
//

$cookie_admin = $HTTP_COOKIE_VARS['spip_admin'];
$admin_ok = ($cookie_admin != '');
if ($admin_ok AND !$flag_preserver AND !$flag_boutons_admin) {
	include_local("inc-admin.php3");
	afficher_boutons_admin();
}


// envoyer la page si possible
@flush();


// ---------------------------------------------------------------------------------------------
// Taches de fond


//
// Envoi du mail quoi de neuf
//
if (!$timeout AND lire_meta('quoi_de_neuf') == 'oui' AND $jours_neuf = lire_meta('jours_neuf')
	AND $adresse_neuf = lire_meta('adresse_neuf') AND (time() - ($majnouv = lire_meta('majnouv'))) > 3600 * 24 * $jours_neuf) {

	include_ecrire('inc_connect.php3');
	if ($db_ok) {
		// lock && indication du prochain envoi
		include_ecrire('inc_meta.php3');
		lire_metas();	// on force la relecture dans la base pour eviter des acces concurrence
		if ($majnouv != lire_meta('majnouv')) {
			spip_log("envoi mail nouveautes: acces concurrent");
			exit;
		}

		ecrire_meta('majnouv', time());
		ecrire_metas();

		// preparation mail
		unset ($mail_nouveautes);
		unset ($sujet_nouveautes);
		$fond = 'nouveautes';
		$delais = 0;
		include(inclure_fichier($fond, $delais, $contexte_inclus));

		// envoi
		if ($mail_nouveautes) {
			spip_log("envoi mail nouveautes");
			include_ecrire('inc_mail.php3');
			envoyer_mail($adresse_neuf, $sujet_nouveautes, $mail_nouveautes);
		} else
			spip_log("envoi mail nouveautes : pas de nouveautes");
	}
	$timeout = true;
}


//
// Faire du menage dans le cache (effacer les fichiers tres anciens)
// Se declenche une fois par jour quand le cache n'est pas recalcule
//
if (!$timeout AND $use_cache AND file_exists('CACHE/.purge2')) {
	include_ecrire('inc_connect.php3');
	if ($db_ok) {
		unlink('CACHE/.purge2');
		spip_log("purge cache niveau 2");
		$query = "SELECT fichier FROM spip_forum_cache WHERE maj < DATE_SUB(NOW(), INTERVAL 14 DAY)";
		$result = spip_query($query);
		unset($fichiers);
		while ($row = spip_fetch_array($result)) {
			$fichier = $row['fichier'];
			if (!file_exists("CACHE/$fichier")) $fichiers[] = "'$fichier'";
		}
		if ($fichiers) {
			$query = "DELETE FROM spip_forum_cache WHERE fichier IN (".join(',', $fichiers).")";
			spip_query($query);
		}
		$timeout = true;
	}
}
if (!$timeout AND $use_cache AND file_exists('CACHE/.purge')) {
	include_ecrire('inc_connect.php3');
	if ($db_ok) {
		unlink('CACHE/.purge');
		spip_log("purge cache niveau 1");
		$f = fopen('CACHE/.purge2', 'w');
		fclose($f);
		include_local ("inc-cache.php3");
		purger_repertoire('CACHE', 14 * 24 * 3600);
		$timeout = true;
	}
}


//
// Archivage des statistiques du site public
//

if (!$timeout AND lire_meta("activer_statistiques") != "non") {
	include_local ("inc-stats.php3");
	archiver_stats();
}


//
// Gerer l'indexation automatique
//

if (lire_meta('activer_moteur') == 'oui') {
	$fichier_index = 'ecrire/data/.index';
	if ($db_ok) {
		include_ecrire("inc_index.php3");
		$s = '';
		if ($id_article AND !deja_indexe('article', $id_article))
			$s .= "article $id_article\n";
		if ($id_auteur AND !deja_indexe('auteur', $id_auteur))
			$s .= "auteur $id_auteur\n";
		if ($id_breve AND !deja_indexe('breve', $id_breve))
			$s .= "breve $id_breve\n";
		if ($id_mot AND !deja_indexe('mot', $id_mot))
			$s .= "mot $id_mot\n";
		if ($id_rubrique AND !deja_indexe('rubrique', $id_rubrique))
			$s .= "rubrique $id_rubrique\n";
		if ($s) {
			$f = fopen($fichier_index, 'a');
			fputs($f, $s);
			fclose($f);
		}
	}
	if (!$timeout AND $use_cache AND file_exists($fichier_index) AND filesize($fichier_index)) {
		include_ecrire("inc_connect.php3");
		if ($db_ok) {
			include_ecrire("inc_texte.php3");
			include_ecrire("inc_filtres.php3");
			include_ecrire("inc_index.php3");
			$suite = file($fichier_index);
			$s = $suite[0];
			$f = fopen($fichier_index, 'w');
			while (list(,$ligne) = each($suite))
				if ($ligne <> $s)
					fwrite($f, $ligne);
			fclose($f);
			$s = explode(' ', trim($s));
			spip_log("indexation $s[0] $s[1]");
			indexer_objet($s[0], $s[1], false);
			$timeout = true;
		}
	}
}


//
// Mise a jour d'un (ou de zero) site syndique
//

if ($db_ok AND lire_meta("activer_syndic") != "non") {
	include_ecrire("inc_texte.php3");
	include_ecrire("inc_filtres.php3");
	include_ecrire("inc_sites.php3");
	include_ecrire("inc_index.php3");

	executer_une_syndication();
	if (lire_meta('activer_moteur') == 'oui' AND !$timeout) {
		executer_une_indexation_syndic();
		$timeout = true;
	}
}


//
// Gestion des statistiques du site public
// (a la fin pour ne pas forcer le $db_ok)
//

if (lire_meta("activer_statistiques") != "non") {
	include_local ("inc-stats.php3");
	ecrire_stats();
}


?>
