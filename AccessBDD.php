<?php

include_once("ConnexionPDO.php");

/**
 * Classe de construction des requêtes SQL à envoyer à la BDD
 */
class AccessBDD
{

    public $login = "root";
    public $mdp = "";
    public $bd = "mediatek86";
    public $serveur = "localhost";
    public $port = "3306";
    public $conn = null;

    /**
     * constructeur : demande de connexion à la BDD
     */
    public function __construct()
    {
        try {
            $this->conn = new ConnexionPDO($this->login, $this->mdp, $this->bd, $this->serveur, $this->port);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * récupération de toutes les lignes d'une table
     * @param string $table nom de la table
     * @return lignes de la requete
     */
    public function selectAll($table)
    {
        if ($this->conn != null) {
            switch ($table) {
                case "livre":
                    return $this->selectAllLivres();
                case "dvd":
                    return $this->selectAllDvd();
                case "revue":
                    return $this->selectAllRevues();
                case "exemplaire":
                    return $this->selectAllExemplaires();
                case "commandedocument":
                    return $this->selectAllCommandesDocuments();
                case "suivi":
                    return $this->selectAllSuiviCommande();
                case "genre":
                case "public":
                case "rayon":
                case "etat":
                    // select portant sur une table contenant juste id et libelle
                    return $this->selectTableSimple($table);
                default:
                    // select portant sur une table, sans condition
                    return $this->selectTable($table);
            }
        } else {
            return null;
        }
    }

    /**
     * récupération des lignes concernées
     * @param string $table nom de la table
     * @param array $champs nom et valeur de chaque champs de recherche
     * @return lignes répondant aux critères de recherches
     */
    public function select($table, $champs)
    {
        if ($this->conn != null && $champs != null) {
            switch ($table) {
                case "exemplaire":
                    return $this->selectExemplairesRevue($champs['id']);
                case "commandedocument":
                    return $this->selectCommandesDocument($champs['id']);
                case "suivi":
                    return $this->selectSuiviCommande($champs['id']);
                default:
                    // cas d'un select sur une table avec recherche sur des champs
                    return $this->selectTableOnConditons($table, $champs);
            }
        } else {
            return null;
        }
    }

    /**
     * récupération de toutes les lignes d'une table simple (qui contient juste id et libelle)
     * @param string $table
     * @return lignes triées sur lebelle
     */
    public function selectTableSimple($table)
    {
        $req = "select * from $table order by libelle;";
        return $this->conn->query($req);
    }

    /**
     * récupération de toutes les lignes d'une table
     * @param string $table
     * @return toutes les lignes de la table
     */
    public function selectTable($table)
    {
        $req = "select * from $table;";
        return $this->conn->query($req);
    }

    /**
     * récupération des lignes d'une table dont les champs concernés correspondent aux valeurs
     * @param type $table
     * @param type $champs
     * @return type
     */
    public function selectTableOnConditons($table, $champs)
    {
        // construction de la requête
        $requete = "select * from $table where ";
        foreach ($champs as $key => $value) {
            $requete .= "$key=:$key and";
        }
        // (enlève le dernier and)
        $requete = substr($requete, 0, strlen($requete) - 3);
        return $this->conn->query($requete, $champs);
    }

    /**
     * récupération de toutes les lignes de la table Livre et les tables associées
     * @return lignes de la requete
     */
    public function selectAllLivres()
    {
        $req = "Select l.id, l.ISBN, l.auteur, d.titre, d.image, l.collection, ";
        $req .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $req .= "from livre l join document d on l.id=d.id ";
        $req .= "join genre g on g.id=d.idGenre ";
        $req .= "join public p on p.id=d.idPublic ";
        $req .= "join rayon r on r.id=d.idRayon ";
        $req .= "order by titre ";
        return $this->conn->query($req);
    }

    /**
     * récupération de toutes les lignes de la table DVD et les tables associées
     * @return lignes de la requete
     */
    public function selectAllDvd()
    {
        $req = "Select l.id, l.duree, l.realisateur, d.titre, d.image, l.synopsis, ";
        $req .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $req .= "from dvd l join document d on l.id=d.id ";
        $req .= "join genre g on g.id=d.idGenre ";
        $req .= "join public p on p.id=d.idPublic ";
        $req .= "join rayon r on r.id=d.idRayon ";
        $req .= "order by titre ";
        return $this->conn->query($req);
    }

    /**
     * récupération de toutes les lignes de la table Revue et les tables associées
     * @return lignes de la requete
     */
    public function selectAllRevues()
    {
        $req = "Select l.id, l.periodicite, d.titre, d.image, l.delaiMiseADispo, ";
        $req .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $req .= "from revue l join document d on l.id=d.id ";
        $req .= "join genre g on g.id=d.idGenre ";
        $req .= "join public p on p.id=d.idPublic ";
        $req .= "join rayon r on r.id=d.idRayon ";
        $req .= "order by titre ";
        return $this->conn->query($req);
    }

    /**
     * récupération de toutes les lignes de la table Revue et les tables associées
     * @return lignes de la requete
     */
    public function selectAllExemplaires()
    {
        $req = "Select l.id, l.numero, l.dateAchat, l.photo, l.idEtat, d.titre, d.image ";
        $req .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $req .= "from exemplaire l join document d on l.id=d.id ";
        $req .= "join genre g on g.id=d.idGenre ";
        $req .= "join public p on p.id=d.idPublic ";
        $req .= "join rayon r on r.id=d.idRayon ";
        $req .= "order by titre ";
        return $this->conn->query($req);
    }

    /**
     * récupération de toutes les lignes de la table Revue et les tables associées
     * @return lignes de la requete
     */
    public function selectAllCommandesDocument()
    {
        $req = "Select l.id, l.montant, l.dateCommande, d.titre, d.image ";
        $req .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $req .= "from commande l join document d on l.id=d.id ";
        $req .= "join genre g on g.id=d.idGenre ";
        $req .= "join public p on p.id=d.idPublic ";
        $req .= "join rayon r on r.id=d.idRayon ";
        $req .= "order by titre ";
        return $this->conn->query($req);
    }

    /**
     * récupération de toutes les lignes de la table Suivi et les tables associées
     * @return lignes de la requete
     */
    public function selectAllSuiviCommande()
    {
        $req = "Select l.id, l.etapeSuivi, l.commandeDocumentId, d.titre, d.image ";
        $req .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $req .= "from suivi l join document d on l.id=d.id ";
        $req .= "join genre g on g.id=d.idGenre ";
        $req .= "join public p on p.id=d.idPublic ";
        $req .= "join rayon r on r.id=d.idRayon ";
        $req .= "order by titre ";
        return $this->conn->query($req);
    }

    /**
     * récupération de tous les exemplaires d'une revue
     * @param string $id id de la revue
     * @return lignes de la requete
     */
    public function selectExemplairesRevue($id)
    {
        $param = array(
            "id" => $id
        );
        $req = "Select e.id, e.numero, e.dateAchat, e.photo, e.idEtat ";
        $req .= "from exemplaire e join document d on e.id=d.id ";
        $req .= "where e.id = :id ";
        $req .= "order by e.dateAchat DESC";
        return $this->conn->query($req, $param);
    }

    /**
     * suppresion d'une ou plusieurs lignes dans une table
     * @param string $table nom de la table
     * @param array $champs nom et valeur de chaque champs
     * @return true si la suppression a fonctionné
     */
    public function delete($table, $champs)
    {
        if ($this->conn != null) {
            switch ($table) {
                case "lacommandedocument":
                    return $this->doubleDelete($table, $champs);
                default:
                    // construction de la requête
                    $requete = "delete from $table where ";
                    foreach ($champs as $key => $value) {
                        $requete .= "$key=:$key and ";
                    }
                    // (enlève le dernier and)
                    $requete = substr($requete, 0, strlen($requete) - 5);
                    return $this->conn->execute($requete, $champs);
            }
        } else {
            return null;
        }
    }

    /**
     * suppression d'une ligne dans deux tables
     * @param string $table nom de la table
     * @param array $champs nom et valeur de chaque champs de la ligne
     * @return true si l'ajout a fonctionné
     */
    public function doubleDelete($table, $champs)
    {
        if ($this->conn != null && $champs != null && $table != null) {
            // Tableau des champs pour la table "commande"
            $champsCommande = ['id' => $champs['Id'], 'dateCommande' => $champs['DateCommande'], 'montant' => $champs['Montant']];
            $champsCommandeDoc = ['id' => $champs['Id'], 'nbExemplaire' => $champs['NbExemplaire'], 'idLivreDvd' => $champs['IdLivreDvd']];
            $champsSuivi = ['id' => $champs['Id'], 'etapeSuivi' => $champs['EtapeSuivi']];
            return $this->delete('suivi', $champsSuivi) && $this->delete('commandedocument', $champsCommandeDoc) && $this->delete('commande', $champsCommande);
        } else {
            return null;
        }
    }

    /**
     * ajout d'une ligne dans une table
     * @param string $table nom de la table
     * @param array $champs nom et valeur de chaque champs de la ligne
     * @return true si l'ajout a fonctionné
     */
    public function insertOne($table, $champs)
    {
        if ($this->conn != null && $champs != null) {
            switch ($table) {
                case "lacommandedocument":
                    return $this->doubleInsert($table, $champs);
                default:
                    // construction de la requête
                    $requete = "insert into $table (";
                    foreach ($champs as $key => $value) {
                        $requete .= "$key,";
                    }
                    // (enlève la dernière virgule)
                    $requete = substr($requete, 0, strlen($requete) - 1);
                    $requete .= ") values (";
                    foreach ($champs as $key => $value) {
                        $requete .= ":$key,";
                    }
                    // (enlève la dernière virgule)
                    $requete = substr($requete, 0, strlen($requete) - 1);
                    $requete .= ");";
                    return $this->conn->execute($requete, $champs);
            }
        } else {
            return null;
        }
    }


    /**
     * ajout d'une ligne dans deux tables
     * @param string $table nom de la table
     * @param array $champs nom et valeur de chaque champs de la ligne
     * @return true si l'ajout a fonctionné
     */
    public function doubleInsert($table, $champs)
    {
        if ($this->conn != null && $champs != null && $table != null) {
            // Tableau des champs pour la table "commande"
            $champsCommande = ['id' => $champs['Id'], 'dateCommande' => $champs['DateCommande'], 'montant' => $champs['Montant']];
            $champsCommandeDoc = ['id' => $champs['Id'], 'nbExemplaire' => $champs['NbExemplaire'], 'idLivreDvd' => $champs['IdLivreDvd']];
            $champsSuivi = ['id' => $champs['Id'], 'etapeSuivi' => $champs['EtapeSuivi']];
            return $this->insertOne('commande', $champsCommande) && $this->insertOne('commandedocument', $champsCommandeDoc) && $this->insertOne('suivi', $champsSuivi);
        } else {
            return null;
        }
    }

    /**
     * modification d'une ligne dans une table
     * @param string $table nom de la table
     * @param string $id id de la ligne à modifier
     * @param array $param nom et valeur de chaque champs de la ligne
     * @return true si la modification a fonctionné
     */
    public function updateOne($table, $id, $champs)
    {
        if ($this->conn != null && $champs != null) {
            // construction de la requête
            $requete = "update $table set ";
            foreach ($champs as $key => $value) {
                $requete .= "$key=:$key,";
            }
            // (enlève la dernière virgule)
            $requete = substr($requete, 0, strlen($requete) - 1);
            $champs["id"] = $id;
            $requete .= " where id=:id;";
            return $this->conn->execute($requete, $champs);
        } else {
            return null;
        }
    }

    /**
     * récupèration des informations d'un livre sur le suivi des commandes.
     * @return lignes de la requete
     */
    public function selectCommandesDocument($id)
    {
        $param = array(
            "id" => $id
        );
        $req = "select l.nbExemplaire, l.idLivreDvd, s.etapeSuivi, l.id, c.dateCommande, c.montant ";
        $req .= "from commande c ";
        $req .= "left join commandedocument l on c.id=l.id ";
        $req .= "left join suivi s on c.id=s.id ";
        $req .= "where l.idLivreDvd = :id ";
        $req .= "group by l.id ";
        $req .= "order by c.dateCommande DESC";
        return $this->conn->query($req, $param);
    }

    /**
     * récupèration de toutes les commandes.
     * @return lignes de la requete
     */
    public function selectAllCommandesDocuments()
    {
        $req = "select l.nbExemplaire, l.idLivreDvd, s.etapeSuivi, l.id, c.dateCommande, c.montant ";
        $req .= "from commande c ";
        $req .= "join commandedocument l on c.id=l.id ";
        $req .= "left join suivi s on c.id=s.id ";
        $req .= "group by l.id ";
        $req .= "order by c.dateCommande DESC";
        return $this->conn->query($req);
    }

    /**
     * récupèration des informations d'un livre sur le suivi d'une commande.
     * @return lignes de la requete
     */
    public function selectSuiviCommande($id)
    {
        $param = array(
            "id" => $id
        );
        $req = "select l.nbExemplaire, l.idLivreDvd, l.idSuivi, s.libelle, l.id, max(c.dateCommande) asdateCommande, sum(c.montant) as montant ";
        $req .= "from commandedocument l join suivi s on s.id=l.idSuivi ";
        $req .= "left join commande c on l.id=c.id ";
        $req .= "where l.idLivreDvd = :id ";
        $req .= "group by l.id ";
        $req .= "order by dateCommande DESC";
        return $this->conn->query($req, $param);
    }
}
