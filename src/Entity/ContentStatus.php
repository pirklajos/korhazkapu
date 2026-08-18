<?php
declare(strict_types=1);
namespace App\Entity;
enum ContentStatus:string { case Draft='draft'; case MedicalReview='medical_review'; case CommunicationReview='communication_review'; case Approved='approved'; case Published='published'; case Expired='expired'; case Archived='archived'; }
