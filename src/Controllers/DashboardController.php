<?php

namespace Controllers;

use \_assets\Includes\HandleSessionActive;
use \_assets\Includes\DatabaseConnection;
use PDOException;
use \Views\Dashboard;
use \Models\UserRepository;
use \Models\FormRepository;
use \Models\AnswerRepository;

class DashboardController extends HandleSessionActive
{
    public function __construct() {
        parent::__construct();
    }
    public function execute(): void
    {

        $this->requireLogin();

        try {
            $userRepository = new UserRepository($this->db);
            $formRepository = new FormRepository($this->db);
            $answerRepository = new AnswerRepository($this->db);
        } catch (PDOException $e) {
            (new \Views\Error("Erreur: BDD", "Connexion à la base de donnée impossible", "/"))->show();
            return;
        }

        $userId = $_SESSION['user_id'];
        $username = $this->user->getUsername();

        $page = max(1, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1);
        $limit = 6;
        $offset = ($page - 1) * $limit;

        $nb_form = $formRepository->findNumberOfFormPerUser($userId);
        $nb_answer = $answerRepository->findNumberOfAnswerPerUser($userId);
        $user_mail = $userRepository->findById($userId)->getEmail();

        $totalPages = (int) ceil($nb_form / $limit);

        $infos_form = $formRepository->findFormInformations($userId, $limit, $offset);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return;
        }

        (new Dashboard($username))->show($nb_form, $nb_answer, $user_mail, $infos_form, $page, $totalPages);
    }
}