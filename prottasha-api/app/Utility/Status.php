<?php

namespace App\Utility;

final class Status
{
    public const ALLOW_ROLE = [1, 5, 7, 8, 12];

    public const ALLOW_ROLE_NAME = [
        1 => 'Super Admin',
        5 => 'Ministry Admin',
        7 => 'Office Admin',
        8 => 'Section Admin',
        12 => 'Municipal Admin'
    ];

    public const SUPER_ADMIN = 1;
    public const MINISTRY_ADMIN = 5;
    public const OFFICE_ADMIN = 7;
    public const MUNICIPAL_ADMIN = 12;


    public const GENDER = [1 => 'Male',2 => 'Female',3 => 'Common',4 => 'Not Mentioned'];

    public const VULNERABILITY = ['Elderly','Minor','IP','PWD','Pregnant'];

    public const CITIZENSTAUS = [0 => 'Non Verified',1 => 'Verified',2 => 'Reject',3 => 'Permanent Reject'];


    public const STATUS = [0 => 'Non Verified',1 => 'Verified',2 => 'Reject',3 => 'Permanent Reject'];


    public const DECISIONLIST = [
        '1' => 'Forward',
        '2' => 'Return',
        '3' => 'Reject',
        '4' => 'Complete',
        '5' => 'Resubmit',
        '6' => 'Print',
        '7' => 'PaymentRequest',
        '8' => 'PaymentSuccess',
        '9' => 'PaymentResubmit',
        '10' => 'Received',
        '11' => 'In-Progress',
        '12' => 'Generate Certificate',
        '13' => 'Cancel',
        '15' => 'Document Request',
        '16' => 'Document Received',
        '17' => 'Document Success',
        '18' => 'Document Resubmit'
    ];

}
