<?php

include ("inc.php3");
include ("inc_statistiques.php3");


if ($id_article){
	$query = "SELECT titre, visites, popularite FROM spip_articles WHERE statut='publie' AND id_article ='$id_article'";
	$result = spip_query($query);

	if ($row = mysql_fetch_array($result)) {
		$titre = typo($row['titre']);
		$total_absolu = $row['visites'];
		$val_popularite = round($row['popularite']);
	}
} 
else {
	$query = "SELECT SUM(visites) AS total_absolu FROM spip_visites";
	$result = spip_query($query);

	if ($row = mysql_fetch_array($result)) {
		$total_absolu = $row['total_absolu'];
	}
}


if($titre) $pourarticle = " pour &laquo; $titre &raquo;";

debut_page("Statistiques des visites".$pourarticle, "administration", "statistiques");

echo "<br><br><br>";
gros_titre("&Eacute;volution des visites<html>".aide("confstat")."</html>");
barre_onglets("statistiques", "evolution");

if ($titre) gros_titre($titre);

debut_gauche();



	echo "<p>";

	echo "<div class='iconeoff' style='padding: 5px;'>";
	echo "<font face='Verdana,Arial,Helvetica,sans-serif' size=2>";
	echo typo("Afficher les visites pour:");
	echo "<ul>";
	if ($id_article>0) {
		echo "<li><b><a href='statistiques_visites.php3'>Tout le site</a></b>";
	} else {
		echo "<li><b>Tout le site</b>";
	}

		echo "</ul>";
		echo "</font>";
		echo "</div>";

	
	// Par popularite
	$articles_recents[] = "0";
	$query = "SELECT id_article FROM spip_articles WHERE statut='publie' AND popularite > 0 ORDER BY date DESC LIMIT 0,20";
	$result = spip_query($query);
	while ($row = mysql_fetch_array($result)) {
		$articles_recents[] = $row['id_article'];
	}
	$articles_recents = join($articles_recents, ",");
		
	// Par popularite
	$query = "SELECT id_article, titre, popularite, visites FROM spip_articles WHERE statut='publie' AND popularite > 0 ORDER BY popularite DESC";
	$result = spip_query($query);

	$nombre_articles = mysql_num_rows($result);
	if ($nombre_articles > 0) {
		echo "<p>";
		echo "<div class='iconeoff' style='padding: 5px;'>";
		echo "<font face='Verdana,Arial,Helvetica,sans-serif' size=2>";
		echo typo("Afficher les visites pour <b>les articles les plus populaires</b> et pour <b>les derniers articles publi&eacute;s&nbsp;:</b>");
		echo "<ol style='padding-left:25 px;'>";
		echo "<font size=1 color='#666666'>";
		while ($row = mysql_fetch_array($result)) {
			$titre = typo($row['titre']);
			$l_article = $row['id_article'];
			$visites = $row['visites'];
			$popularite = round($row['popularite']);
			$liste++;
			$classement[$l_article] = $liste;
			
			if ($liste <= 20) {
				$articles_vus[] = $l_article;
			
				if ($l_article == $id_article){
					echo "\n<li value='$liste'><b>$titre</b>";
				} else {
					echo "\n<li value='$liste'><a href='statistiques_visites.php3?id_article=$l_article' title='popularit&eacute;&nbsp;:&nbsp;$popularite&nbsp;; visites&nbsp;:&nbsp;$visites'>$titre</a>";
				}
			}
		}
		$articles_vus = join($articles_vus, ",");
			
		// Par popularite
		$query_suite = "SELECT id_article, titre, popularite, visites FROM spip_articles WHERE statut='publie' AND id_article IN ($articles_recents) AND id_article NOT IN ($articles_vus) ORDER BY popularite DESC";
		$result_suite = spip_query($query_suite);
		
		if (mysql_num_rows($result_suite) > 0) {
			echo "<br><br>[...]<br><br>";
			while ($row = mysql_fetch_array($result_suite)) {
				$titre = typo($row['titre']);
				$l_article = $row['id_article'];
				$visites = $row['visites'];
				$popularite = round($row['popularite']);
				$numero = $classement[$l_article];
				
				if ($l_article == $id_article){
					echo "\n<li value='$numero'><b>$titre</b></li>";
				} else {
					echo "\n<li value='$numero'><a href='statistiques_visites.php3?id_article=$l_article' title='popularit&eacute;&nbsp;:&nbsp;$popularite&nbsp;; visites&nbsp;:&nbsp;$visites'>$titre</a></li>";
				}
			}
		}
			
		echo "</ol>";

		echo "<b>Comment lire ce tableau</b><br>Le rang de l'article,
		dans le classement par popularit&eacute;, est indiqu&eacute; dans la
		marge&nbsp;; la popularit&eacute; de l'article (une estimation du
		nombre de visites quotidiennes qu'il recevra si le rythme actuel de
		consultation se maintient) et le nombre de visites re&ccedil;ues
		depuis le d&eacute;but sont affich&eacute;es dans la bulle qui
		appara&icirc;t lorsque la souris survole le titre.";

		echo "</font>";
		echo "</font>";
		echo "</div>";
	}
		










	
		// Par visites depuis le debut
	$query = "SELECT id_article, titre, popularite, visites FROM spip_articles WHERE statut='publie' AND popularite > 0 ORDER BY visites DESC LIMIT 0,30";
	$result = spip_query($query);
		
	if (mysql_num_rows($result) > 0) {
	creer_colonne_droite();

		echo "<div class='iconeoff' style='padding: 5px;'>";
		echo "<font face='Verdana,Arial,Helvetica,sans-serif' size=2>";
		echo typo("Afficher les visites pour <b>les articles les plus visit&eacute;s depuis le d&eacute;but&nbsp;:</b>");
		echo "<ol style='padding-left:25 px;'>";
		echo "<font size=1 color='#666666'>";

		while ($row = mysql_fetch_array($result)) {
			$titre = typo($row['titre']);
			$l_article = $row['id_article'];
			$visites = $row['visites'];
			$popularite = round($row['popularite']);
				$numero = $classement[$l_article];
				
				if ($l_article == $id_article){
					echo "\n<li value='$numero'><b>$titre</b></li>";
				} else {
					echo "\n<li value='$numero'><a href='statistiques_visites.php3?id_article=$l_article' title='popularit&eacute;&nbsp;:&nbsp;$popularite&nbsp;; visites&nbsp;:&nbsp;$visites'>$titre</a></li>";
				}
		}
		echo "</ol>";
		echo "</font>";
	
		echo "</font>";
		echo "</div>";
	}




//
// Afficher les boutons de creation d'article et de breve
//
if ($connect_statut == '0minirezo') {
	debut_raccourcis();
	
	if ($id_article > 0){
	icone_horizontale("Retour &agrave; l'article", "articles.php3?id_article=$id_article", "article-24.gif","rien.gif");
	}
	icone_horizontale("Suivi des forums", "controle_forum.php3", "suivi-forum-24.gif", "rien.gif");
	
	fin_raccourcis();
}



debut_droite();

if ($connect_statut != '0minirezo') {
	echo "Vous n'avez pas acc&egrave;s &agrave; cette page.";
	fin_page();
	exit;
}




//////

if ($id_article) {
	$table = "spip_visites_articles";
	$table_ref = "spip_referers_articles";
	$where = "id_article=$id_article";
}
else {
	$table = "spip_visites";
	$table_ref = "spip_referers";
	$where = "1";
}



$query="SELECT UNIX_TIMESTAMP(date) AS date_unix, visites FROM $table ".
	"WHERE $where AND date > DATE_SUB(NOW(),INTERVAL 420 DAY) ORDER BY date";
$result=spip_query($query);

while ($row = mysql_fetch_array($result)) {
	$date = $row['date_unix'];
	$visites = $row['visites'];

	$log[$date] = $visites;
	if ($i == 0) $date_debut = $date;
	$i++;
}

// Visites du jour
if ($id_article) {
	$query = "SELECT COUNT(DISTINCT ip) AS visites FROM spip_visites_temp WHERE type = 'article' AND id_objet = $id_article";
	$result = spip_query($query);
}
else {
	$query = "SELECT COUNT(DISTINCT ip) AS visites FROM spip_visites_temp";
	$result = spip_query($query);
}
if ($row = @mysql_fetch_array($result)) {
	$visites_today = $row['visites'];
}
else
	$visites_today = 0;

if (count($log)>0){

	$max = max(max($log),$visites_today);
	$date_today = time();

	$nb_jours = floor(($date_today-$date_debut)/(3600*24));

	
	$maxgraph = substr(ceil(substr($max,0,2) / 10)."000000000000", 0, strlen($max));
	
	if ($maxgraph < 10) $maxgraph = 10;
	if (1.1 * $maxgraph < $max) $maxgraph.="0";	

	if (0.8*$maxgraph > $max) $maxgraph = 0.8 * $maxgraph;

	$rapport = 200 / $maxgraph;

	if (count($log) < 420) $largeur = floor(420 / ($nb_jours+1));
	if ($largeur < 1) $largeur = 1;

	debut_cadre_relief("statistiques-24.gif");
	echo "<table cellpadding=0 cellspacing=0 border=0><tr><td background='img_pack/fond-stats.gif'>";
	echo "<table cellpadding=0 cellspacing=0 border=0><tr>";

		echo "<td bgcolor='black'><img src='img_pack/rien.gif' width=1 height=200></td>";

	// Presentation graphique
	while (list($key, $value) = each($log)) {
		$n++;
		
		if ($decal == 30) $decal = 0;
		$decal ++;
		$tab_moyenne[$decal] = $value;
		
		//inserer des jours vides si pas d'entrees	
		if ($jour_prec > 0) {
			$ecart = floor(($key-$jour_prec)/(3600*24)-1);
	
			for ($i=0; $i < $ecart; $i++){
				if ($decal == 30) $decal = 0;
				$decal ++;
				$tab_moyenne[$decal] = $value;
				$moyenne = array_sum($tab_moyenne) / count($tab_moyenne);
	
				$hauteur_moyenne = round(($moyenne) * $rapport) - 1;
				echo "<td valign='bottom' width=$largeur>";
				$difference = ($hauteur_moyenne) -1;
				if ($difference > 0) {	
					echo "<img src='img_pack/rien.gif' width=$largeur height=1 style='background-color:#333333;'>";
					echo "<img src='img_pack/rien.gif' width=$largeur height=$hauteur_moyenne>";
				}
				echo "<img src='img_pack/rien.gif' width=$largeur height=1 style='background-color:black;'>";
				echo "</td>";
				$n++;
			}
		}
		$total_loc = $total_loc + $value;
		$moyenne = array_sum($tab_moyenne) / count($tab_moyenne);
		$hauteur_moyenne = round($moyenne * $rapport) - 1;
		$hauteur = round($value * $rapport)	- 1;
		echo "<td valign='bottom' width=$largeur>";
		
		if ($hauteur > 0){
			if ($hauteur_moyenne > $hauteur) {
				$difference = ($hauteur_moyenne - $hauteur) -1;
				echo "<img src='img_pack/rien.gif' width=$largeur height=1 style='background-color:#333333;'>";
				echo "<img src='img_pack/rien.gif' width=$largeur height=$difference>";
				echo "<img src='img_pack/rien.gif' width=$largeur height=1 style='background-color:$couleur_foncee;'>";
				if (date("w",$key) == "0"){ // Dimanche en couleur foncee
					echo "<img src='img_pack/rien.gif' width=$largeur height=$hauteur style='background-color:$couleur_foncee;'>";
				} 
				else {
					echo "<img src='img_pack/rien.gif' width=$largeur height=$hauteur style='background-color:$couleur_claire;'>";
				}
			}
			else if ($hauteur_moyenne < $hauteur) {
				$difference = ($hauteur - $hauteur_moyenne) -1;
				echo "<img src='img_pack/rien.gif' width=$largeur height=1 style='background-color:$couleur_foncee;'>";
				if (date("w",$key) == "0"){ // Dimanche en couleur foncee
					$couleur =  $couleur_foncee;
				} 
				else {
					$couleur = $couleur_claire;
				}
				echo "<img src='img_pack/rien.gif' width=$largeur height=$difference style='background-color:$couleur;'>";
				echo "<img src='img_pack/rien.gif' width=$largeur height=1 style='background-color:#333333;'>";
				echo "<img src='img_pack/rien.gif' width=$largeur height=$hauteur_moyenne style='background-color:$couleur;'>";
			}
			else {
				echo "<img src='img_pack/rien.gif' width=$largeur height=1 style='background-color:$couleur_foncee;'>";
				if (date("w",$key) == "0"){ // Dimanche en couleur foncee
					echo "<img src='img_pack/rien.gif' width=$largeur height=$hauteur style='background-color:$couleur_foncee;'>";
				} 
				else {
					echo "<img src='img_pack/rien.gif' width=$largeur height=$hauteur style='background-color:$couleur_claire;'>";
				}
			}
		}
		echo "<img src='img_pack/rien.gif' width=$largeur height=1 style='background-color:black;'>";
		echo "</td>\n";
		
		$jour_prec = $key;
		$val_prec = $value;
	}
		// Dernier jour
		$hauteur = round($visites_today * $rapport)	- 1;
		$total_absolu = $total_absolu + $visites_today;
		echo "<td valign='bottom' width=$largeur>";
		if ($hauteur > 0){
			echo "<img src='img_pack/rien.gif' width=$largeur height=1 style='background-color:$couleur_foncee;'>";
			echo "<img src='img_pack/rien.gif' width=$largeur height=$hauteur style='background-color:#eeeeee;'>";
		}
		echo "<img src='img_pack/rien.gif' width=$largeur height=1 style='background-color:black;'>";
		echo "</td>";
	
	
	echo "<td bgcolor='black'><img src='img_pack/rien.gif' width=1 height=1></td>";
	echo "</tr></table>";
	echo "</td>";
	echo "<td background='img_pack/fond-stats.gif' valign='bottom'><img src='img_pack/rien.gif' style='background-color:black;' width=3 height=1></td>";
	echo "<td><img src='img_pack/rien.gif' width=5 height=1></td>";
	echo "<td valign='top'><font face='Verdana,Arial,Helvetica,sans-serif' size=2>";
		echo "<table cellpadding=0 cellspacing=0 border=0>";
		echo "<tr><td height=15 valign='top'>";		
		echo "<font face='arial,helvetica,sans-serif' size=1><b>".round($maxgraph)."</b></font>";
		echo "</td></tr>";
		echo "<tr><td height=25 valign='middle'>";		
		echo "<font face='arial,helvetica,sans-serif' size=1 color='#999999'>".round(7*($maxgraph/8))."</font>";
		echo "</td></tr>";
		echo "<tr><td height=25 valign='middle'>";		
		echo "<font face='arial,helvetica,sans-serif' size=1>".round(3*($maxgraph/4))."</font>";
		echo "</td></tr>";
		echo "<tr><td height=25 valign='middle'>";		
		echo "<font face='arial,helvetica,sans-serif' size=1 color='#999999'>".round(5*($maxgraph/8))."</font>";
		echo "</td></tr>";
		echo "<tr><td height=25 valign='middle'>";		
		echo "<font face='arial,helvetica,sans-serif' size=1><b>".round($maxgraph/2)."</b></font>";
		echo "</td></tr>";
		echo "<tr><td height=25 valign='middle'>";		
		echo "<font face='arial,helvetica,sans-serif' size=1 color='#999999'>".round(3*($maxgraph/8))."</font>";
		echo "</td></tr>";
		echo "<tr><td height=25 valign='middle'>";		
		echo "<font face='arial,helvetica,sans-serif' size=1>".round($maxgraph/4)."</font>";
		echo "</td></tr>";
		echo "<tr><td height=25 valign='middle'>";		
		echo "<font face='arial,helvetica,sans-serif' size=1 color='#999999'>".round(1*($maxgraph/8))."</font>";
		echo "</td></tr>";
		echo "<tr><td height=10 valign='bottom'>";		
		echo "<font face='arial,helvetica,sans-serif' size=1><b>0</b></font>";
		echo "</td>";
		
		
		echo "</table>";
	echo "</font></td>";
	echo "</td></tr></table>";
		echo "<font face='arial,helvetica,sans-serif' size=1>(barres fonc&eacute;es :  dimanche / courbe fonc&eacute;e : &eacute;volution de la moyenne)</font>";
		
		echo "<p><table cellpadding=0 cellspacing=0 border=0 width='100%'><tr width='100%'>";
		echo "<td valign='top' width='33%'><font face='Verdana,Arial,Helvetica,sans-serif'>";
		echo "maximum&nbsp;: $max";
		echo "<br>moyenne&nbsp;: ".round($moyenne);
		echo "</td>";
		echo "<td valign='top' width='33%'><font face='Verdana,Arial,Helvetica,sans-serif'>";
		echo "aujourd'hui&nbsp;: $visites_today";
		if ($val_prec > 0) echo "<br>hier&nbsp;: $val_prec";
		if ($id_article) echo "<br>popularit&eacute;&nbsp;: $val_popularite";

		echo "</td>";
		echo "<td valign='top' width='33%'><font face='Verdana,Arial,Helvetica,sans-serif'>";
		echo "<b>total : $total_absolu</b>";
		
		if ($id_article) {
			if ($classement[$id_article] > 0) {
				$er = ($classement[$id_article] == 1) ? "er" : "e";
				echo "<br>".$classement[$id_article]."<sup>$er</sup> sur $liste";
			}
		} else {
			echo "<font size=1>";
			echo "<br>popularit&eacute; du site&nbsp;: ";
			echo ceil(lire_meta('popularite_total'));
			echo "</font>";
		}
		
		
		echo "</td></tr></table>";		
	
	fin_cadre_relief();

}

$activer_statistiques_ref = lire_meta("activer_statistiques_ref");
if ($activer_statistiques_ref != "non"){
	// Affichage des referers

	$query = "SELECT * FROM $table_ref WHERE $where ORDER BY visites DESC LIMIT 0,100";
	$result = spip_query($query);
	
	echo "<p><font face='Verdana,Arial,Helvetica,sans-serif' size=2>";
	while ($row = mysql_fetch_array($result)) {
		$referer = $row['referer'];
		$visites = $row['visites'];
	
		echo "\n<li>";
	
	
		if ($visites > 5) echo "<font color='red'>$visites liens : </font>";
		else if ($visites > 1) echo "$visites liens : ";
		else echo "<font color='#999999'>$visites lien : </font>";
	
		echo stats_show_keywords($referer, $referer);
	}
}
echo "</font>";

fin_page();

?>
