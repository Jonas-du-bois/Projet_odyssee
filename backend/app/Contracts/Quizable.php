<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;
use App\Models\User;

/**
 * Interface pour tous les modules qui peuvent être utilisés dans un quiz
 * Applique le principe KISS : une interface simple et claire
 */
interface Quizable
{
    /**
     * Obtenir les questions pour ce module
     *
     * @param array $options Options pour personnaliser la récupération des questions
     * @return Collection
     */
    public function getQuestions(array $options = []): Collection;

    /**
     * Obtenir le titre du quiz
     *
     * @return string
     */
    public function getQuizTitle(): string;

    /**
     * Obtenir la description du quiz
     *
     * @return string
     */
    public function getQuizDescription(): string;

    /**
     * Vérifier si ce module est disponible pour un utilisateur
     *
     * @param User $user
     * @return bool
     */
    public function isAvailable(User $user): bool;

    /**
     * Obtenir le mode de quiz par défaut pour ce module
     *
     * @return string
     */
    public function getDefaultQuizMode(): string;

    /**
     * Vérifier si ce quiz peut être rejoué
     *
     * @return bool
     */
    public function isReplayable(): bool;
}
