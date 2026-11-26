<?php

/*
|--------------------------------------------------------------------------
| Career guidance: A-level combinations and typical university pathways
|--------------------------------------------------------------------------
| o_level   — CSEE subjects the combination builds on (must match subject names)
| a_level   — subjects studied in Form V–VI (used to detect a Form V–VI
|             student's combination from their marks)
| aptitude  — aptitude-test section the combination leans on
| courses   — typical degree programmes and example institutions.
|
| University entry requirements change every year: always confirm in the
| current TCU Undergraduate Admission Guidebook (www.tcu.go.tz). Edit this
| file to add programmes or institutions.
*/

return [
    'tcu_note' => 'Typical pathways only — confirm current entry requirements in the TCU Undergraduate Admission Guidebook (www.tcu.go.tz).',

    'areas' => [
        'Sciences'    => ['Physics', 'Chemistry', 'Biology'],
        'Mathematics' => ['Basic Mathematics', 'Additional Mathematics'],
        'Languages'   => ['English Language', 'Kiswahili', 'French', 'Literature in English'],
        'Humanities'  => ['History', 'Geography', 'Civics'],
        'Business'    => ['Commerce', 'Book-keeping'],
    ],

    'combinations' => [
        'PCM' => [
            'o_level'  => ['Physics', 'Chemistry', 'Basic Mathematics'],
            'a_level'  => ['Physics', 'Chemistry', 'Advanced Mathematics'],
            'aptitude' => 'Numerical',
            'careers'  => 'Engineering, architecture, computer science, ICT, actuarial science, aviation',
            'courses'  => [
                ['BSc Civil Engineering', 'UDSM, ARU, DIT, MUST'],
                ['BSc Electrical / Mechanical Engineering', 'UDSM, DIT, MUST, UDOM'],
                ['BSc Computer Science / Computer Engineering', 'UDSM, UDOM, NM-AIST'],
                ['Bachelor of Architecture', 'ARU'],
                ['BSc Actuarial Science', 'UDSM, IFM'],
                ['BSc with Education (Mathematics, Physics)', 'UDSM, DUCE, UDOM'],
            ],
        ],
        'PCB' => [
            'o_level'  => ['Physics', 'Chemistry', 'Biology'],
            'a_level'  => ['Physics', 'Chemistry', 'Biology'],
            'aptitude' => 'Numerical',
            'careers'  => 'Medicine, dentistry, pharmacy, nursing, laboratory and biomedical sciences',
            'courses'  => [
                ['Doctor of Medicine (MD)', 'MUHAS, UDOM, KCMUCo, CUHAS'],
                ['Doctor of Dental Surgery', 'MUHAS'],
                ['Bachelor of Pharmacy', 'MUHAS, UDOM, CUHAS'],
                ['BSc Nursing', 'MUHAS, UDOM, KCMUCo'],
                ['BSc Medical Laboratory Sciences', 'MUHAS, KCMUCo, CUHAS'],
                ['BSc Biotechnology / Molecular Biology', 'UDSM, SUA, NM-AIST'],
            ],
        ],
        'PGM' => [
            'o_level'  => ['Physics', 'Geography', 'Basic Mathematics'],
            'a_level'  => ['Physics', 'Geography', 'Advanced Mathematics'],
            'aptitude' => 'Spatial',
            'careers'  => 'Engineering, land surveying, meteorology, geology, mining, urban planning',
            'courses'  => [
                ['BSc Geomatics / Land Surveying', 'ARU'],
                ['BSc Meteorology', 'UDSM'],
                ['BSc Geology / Mining Engineering', 'UDSM, UDOM'],
                ['BSc Urban and Regional Planning', 'ARU'],
                ['BSc Petroleum Engineering', 'UDSM, UDOM'],
            ],
        ],
        'CBG' => [
            'o_level'  => ['Chemistry', 'Biology', 'Geography'],
            'a_level'  => ['Chemistry', 'Biology', 'Geography'],
            'aptitude' => 'Spatial',
            'careers'  => 'Agriculture, environmental science, veterinary, food science, wildlife and forestry',
            'courses'  => [
                ['BSc Agriculture General / Agronomy', 'SUA'],
                ['Doctor of Veterinary Medicine', 'SUA'],
                ['BSc Environmental Science', 'UDSM, SUA, UDOM'],
                ['BSc Food Science and Technology', 'SUA'],
                ['BSc Wildlife Management / Forestry', 'SUA'],
            ],
        ],
        'EGM' => [
            'o_level'  => ['Basic Mathematics', 'Geography', 'Commerce'],
            'a_level'  => ['Economics', 'Geography', 'Advanced Mathematics'],
            'aptitude' => 'Numerical',
            'careers'  => 'Economics, statistics, finance, banking, planning, actuarial',
            'courses'  => [
                ['BA / BSc Economics', 'UDSM, UDOM, Mzumbe'],
                ['BSc Statistics', 'UDSM, UDOM'],
                ['Bachelor of Banking and Finance', 'IFM, UDSM, Mzumbe'],
                ['BSc Economics and Statistics', 'UDSM'],
                ['Bachelor of Development Planning', 'IRDP'],
            ],
        ],
        'ECA' => [
            'o_level'  => ['Commerce', 'Book-keeping', 'Basic Mathematics'],
            'a_level'  => ['Economics', 'Commerce', 'Accountancy'],
            'aptitude' => 'Numerical',
            'careers'  => 'Accounting (CPA), business administration, banking, procurement, tax',
            'courses'  => [
                ['Bachelor of Accounting / BCom Accounting', 'CBE, IFM, UDSM, Mzumbe'],
                ['Bachelor of Business Administration', 'CBE, UDSM, Mzumbe'],
                ['Bachelor of Procurement and Supply', 'CBE, Mzumbe'],
                ['Bachelor of Taxation', 'ITA, IFM'],
                ['Bachelor of Banking and Finance', 'IFM'],
            ],
        ],
        'HGE' => [
            'o_level'  => ['History', 'Geography', 'Basic Mathematics'],
            'a_level'  => ['History', 'Geography', 'Economics'],
            'aptitude' => 'Verbal',
            'careers'  => 'Law, economics, public administration, development studies, planning',
            'courses'  => [
                ['Bachelor of Laws (LLB)', 'UDSM, Mzumbe, UDOM'],
                ['BA Economics', 'UDSM, UDOM'],
                ['Bachelor of Public Administration', 'Mzumbe, UDSM'],
                ['BA Development Studies', 'UDSM, Mzumbe'],
            ],
        ],
        'HGK' => [
            'o_level'  => ['History', 'Geography', 'Kiswahili'],
            'a_level'  => ['History', 'Geography', 'Kiswahili'],
            'aptitude' => 'Verbal',
            'careers'  => 'Teaching, journalism, public administration, community development, tourism',
            'courses'  => [
                ['BA with Education (History, Geography, Kiswahili)', 'UDSM, DUCE, UDOM, MUCE'],
                ['BA Journalism and Mass Communication', 'UDSM, SAUT'],
                ['Bachelor of Public Administration', 'Mzumbe'],
                ['BA Tourism and Cultural Heritage', 'UDSM, OUT'],
                ['BA Community Development', 'UDOM, IRDP'],
            ],
        ],
        'HGL' => [
            'o_level'  => ['History', 'Geography', 'English Language'],
            'a_level'  => ['History', 'Geography', 'English Language'],
            'aptitude' => 'Verbal',
            'careers'  => 'Law, international relations, journalism, tourism, diplomacy',
            'courses'  => [
                ['Bachelor of Laws (LLB)', 'UDSM, Mzumbe, UDOM'],
                ['BA International Relations', 'UDSM, CFR'],
                ['BA Journalism', 'UDSM, SAUT'],
                ['BA with Education (History, Geography, English)', 'UDSM, DUCE, UDOM'],
            ],
        ],
        'HKL' => [
            'o_level'  => ['History', 'Kiswahili', 'English Language'],
            'a_level'  => ['History', 'Kiswahili', 'English Language'],
            'aptitude' => 'Verbal',
            'careers'  => 'Law, mass communication, languages, translation, teaching',
            'courses'  => [
                ['Bachelor of Laws (LLB)', 'UDSM, Mzumbe, Tumaini'],
                ['BA Mass Communication', 'UDSM, SAUT'],
                ['BA Kiswahili / Linguistics', 'UDSM, UDOM'],
                ['BA Translation and Interpretation', 'UDSM'],
                ['BA with Education (Kiswahili, English)', 'UDSM, DUCE, UDOM'],
            ],
        ],
    ],
];
