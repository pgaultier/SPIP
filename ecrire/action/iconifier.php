<?php

/***************************************************************************\
 *  SPIP, Systeme de publication pour l'internet                           *
 *                                                                         *
 *  Copyright (c) 2001-2014                                                *
 *  Arnaud Martin, Antoine Pitrou, Philippe Riviere, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/

/**
 * Gestion de l'action iconifier pour ajouter des logos
 *
 * @package SPIP\Core\Logos
 */

if (!defined('_ECRIRE_INC_VERSION')) return;

/**
 * L'entree par l'action ne sert plus qu'a une retro compat eventuelle
 * le `#FORMULAIRE_EDITER_LOGO` utilise action_spip_image_ajouter_dist
 */
function action_iconifier_dist()
{
	include_spip('inc/actions');
	$securiser_action = charger_fonction('securiser_action', 'inc');
	$arg = $securiser_action();
	$iframe_redirect = _request('iframe_redirect');

	$arg = rawurldecode($arg);

	if (!preg_match(',^-?\d*(\D)(.*)$,',$arg, $r))
		spip_log("action iconifier: $arg pas compris");
	elseif ($r[1] == '+')
		action_spip_image_ajouter_dist($r[2], _request('sousaction2'), _request('source'));
	else	action_spip_image_effacer_dist($r[2]);
	
	if(_request("iframe") == 'iframe') {
		$redirect = urldecode($iframe_redirect)."&iframe=iframe";
		redirige_par_entete(urldecode($redirect));
	}
}

/**
 * Supprime un fichier de logo
 *
 * @param string $arg Fichier de logo (tel que `arton20.png`)
 * @return void
**/
function action_spip_image_effacer_dist($arg) {

	if (!strstr($arg, ".."))
		spip_unlink(_DIR_LOGOS . $arg);
}

//
// Ajouter un logo
//


/**
 * Permet d'ajouter un logo sur un objet, dont on passe le nom 
 * 
 * @example
 *     ```
 *     $logo = &$_FILES['logo_propose'];
 *     include_spip('action/iconifier');
 *     $spip_image_ajouter = charger_fonction('spip_image_ajouter','action');
 *     $spip_image_ajouter('arton'.$id_article,true,$logo);
 *     ```
 * 
 * @param string $arg
 *     Nom de l'image pour l'objet tel que `arton4`
 * @param bool $sousaction2
 *     - false pour prendre automatiquement le logo dans `$_FILES`
 *     - true pour le passer en 3e paramètre
 * @param string $source
 *     Chemin du logo uploadé sur le serveur en attente d'utilisation
 * @return void
**/
function action_spip_image_ajouter_dist($arg,$sousaction2,$source,$return=false) {
	global $formats_logos;

	include_spip('inc/documents');
	if (!$sousaction2) {
		if (!$_FILES) $_FILES = $GLOBALS['HTTP_POST_FILES'];
		$source = (is_array($_FILES) ? array_pop($_FILES) : "");
	}
	$erreur = "";
	if (!$source)
		spip_log("spip_image_ajouter : source inconnue");
	else {
		$f =_DIR_LOGOS . $arg . '.tmp';

		if (!is_array($source)) 
		// fichier dans upload/
	  		$source = @copy(determine_upload() . $source, $f);
		else {
		// Intercepter une erreur a l'envoi
			if ($erreur = check_upload_error($source['error'],"",$return))
				$source ="";
			else
		// analyse le type de l'image (on ne fait pas confiance au nom de
		// fichier envoye par le browser : pour les Macs c'est plus sur)

				$source = deplacer_fichier_upload($source['tmp_name'], $f);
		}
		if (!$source)
			spip_log("pb de copie pour $f");
	}
	if ($source AND $f) {
		$size = @getimagesize($f);
		$type = !$size ? '': ($size[2] > 3 ? '' : $formats_logos[$size[2]-1]);
		if ($type) {
			$poids = filesize($f);

			if (_LOGO_MAX_SIZE > 0
			AND $poids > _LOGO_MAX_SIZE*1024) {
				spip_unlink ($f);
				$erreur = _T('info_logo_max_poids',
									array('maxi' => taille_en_octets(_LOGO_MAX_SIZE*1024),
									'actuel' => taille_en_octets($poids)));
			}

			elseif (_LOGO_MAX_WIDTH * _LOGO_MAX_HEIGHT
			AND ($size[0] > _LOGO_MAX_WIDTH
			OR $size[1] > _LOGO_MAX_HEIGHT)) {
				spip_unlink ($f);
				$erreur = _T('info_logo_max_poids',
									array(
									'maxi' =>
										_T('info_largeur_vignette',
											array('largeur_vignette' => _LOGO_MAX_WIDTH,
											'hauteur_vignette' => _LOGO_MAX_HEIGHT)),
									'actuel' =>
										_T('info_largeur_vignette',
											array('largeur_vignette' => $size[0],
											'hauteur_vignette' => $size[1]))
									));
			}
			else
				@rename ($f, _DIR_LOGOS . $arg . ".$type");
		}
		else {
			spip_unlink ($f);
			$erreur = _T('info_logo_format_interdit',
									array('formats' => join(', ', $formats_logos)));
		}
	
	}
	if ($erreur){
		if ($return)
			return $erreur;
		else
			check_upload_error(6,$erreur);
	}
}
?>
