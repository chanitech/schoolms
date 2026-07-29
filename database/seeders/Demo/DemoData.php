<?php

namespace Database\Seeders\Demo;

/**
 * Reference data for the demo school.
 *
 * Kept apart from the seeder itself so the lists stay readable and can be
 * tweaked without touching the build logic. Names are Tanzanian and the
 * curriculum follows NECTA O-level, because a demo that looks foreign to the
 * person being shown it does not land.
 */
final class DemoData
{
    /** NECTA O-level subject set for a typical secondary school. */
    public const SUBJECTS = [
        ['Civics',            'CIV', 'core'],
        ['History',           'HIS', 'core'],
        ['Geography',         'GEO', 'core'],
        ['Kiswahili',         'KIS', 'core'],
        ['English Language',  'ENG', 'core'],
        ['Basic Mathematics', 'MAT', 'core'],
        ['Biology',           'BIO', 'core'],
        ['Chemistry',         'CHE', 'elective'],
        ['Physics',           'PHY', 'elective'],
        ['Book-keeping',      'BKP', 'elective'],
        ['Commerce',          'COM', 'elective'],
    ];

    /**
     * NECTA grading bands. Point 1 is best, which is why the ordering looks
     * inverted next to a GPA scale.
     */
    public const GRADES = [
        ['A', 75, 100, 1, 'Excellent'],
        ['B', 65, 74.99, 2, 'Very Good'],
        ['C', 45, 64.99, 3, 'Good'],
        ['D', 30, 44.99, 4, 'Satisfactory'],
        ['F', 0, 29.99, 5, 'Fail'],
    ];

    public const DEPARTMENTS = [
        'Languages', 'Mathematics & Science', 'Social Sciences',
        'Business Studies', 'Administration', 'Finance',
    ];

    /** name, role, position, department index, monthly salary (TZS) */
    public const STAFF = [
        ['Emmanuel Mushi',      'Principal',  'Head of School',        4, 1850000],
        ['Neema Kileo',         'Academic',   'Academic Master',       4, 1450000],
        ['Frank Massawe',       'HOD',        'HOD Mathematics & Science', 1, 1250000],
        ['Agnes Mbwana',        'HOD',        'HOD Languages',         0, 1250000],
        ['Josephat Kimaro',     'Teacher',    'Teacher',               1, 950000],
        ['Rehema Ndosi',        'Teacher',    'Teacher',               0, 950000],
        ['Baraka Shirima',      'Teacher',    'Teacher',               1, 920000],
        ['Grace Mollel',        'Teacher',    'Teacher',               2, 900000],
        ['Daniel Lyimo',        'Teacher',    'Teacher',               1, 880000],
        ['Zainab Hamisi',       'Teacher',    'Teacher',               0, 880000],
        ['Peter Macha',         'Teacher',    'Teacher',               2, 860000],
        ['Salome Urassa',       'Teacher',    'Teacher',               3, 860000],
        ['Isaac Mrema',         'Teacher',    'Teacher',               1, 840000],
        ['Happiness Swai',      'Teacher',    'Teacher',               2, 840000],
        ['Method Kessy',        'Dorm Master', 'Dorm Master',          4, 780000],
        ['Furaha Mwakalinga',   'accountant', 'School Accountant',     5, 1100000],
        ['Anna Temu',           'storekeeper', 'Storekeeper',          4, 720000],
        ['Godfrey Sanga',       'HR',         'Human Resources Officer', 4, 980000],
    ];

    public const MALE_FIRST = [
        'Amani', 'Baraka', 'Cosmas', 'Daudi', 'Elias', 'Frank', 'Godfrey', 'Hamisi',
        'Innocent', 'Joachim', 'Kelvin', 'Lameck', 'Method', 'Nickson', 'Onesmo',
        'Peter', 'Raphael', 'Shabani', 'Thomas', 'Upendo', 'Victor', 'Wilfred',
        'Yusuph', 'Zakaria', 'Emmanuel', 'Gerald', 'Juma', 'Nuru', 'Selemani', 'Tumaini',
    ];

    public const FEMALE_FIRST = [
        'Agnes', 'Bahati', 'Christina', 'Devota', 'Esther', 'Flora', 'Grace', 'Happiness',
        'Irene', 'Joyce', 'Kunda', 'Lucia', 'Mariam', 'Neema', 'Oliver',
        'Pendo', 'Rehema', 'Salome', 'Tatu', 'Upendo', 'Veronica', 'Winnie',
        'Yohana', 'Zainab', 'Amina', 'Consolata', 'Furaha', 'Glory', 'Jane', 'Rose',
    ];

    public const MIDDLE = [
        'John', 'Joseph', 'Peter', 'Anna', 'Mary', 'Elias', 'Daniel', 'Sara',
        'Michael', 'Paul', 'Grace', 'James', 'Frank', 'Neema', 'Baraka',
    ];

    public const SURNAMES = [
        'Mushi', 'Kileo', 'Massawe', 'Mbwana', 'Kimaro', 'Ndosi', 'Shirima',
        'Mollel', 'Lyimo', 'Macha', 'Urassa', 'Mrema', 'Swai', 'Kessy',
        'Mwakalinga', 'Temu', 'Sanga', 'Mtei', 'Nkya', 'Chuwa', 'Msuya',
        'Kavishe', 'Mfinanga', 'Minja', 'Assey', 'Marealle', 'Moshi', 'Kimambo',
        'Ngowi', 'Malecela', 'Mrutu', 'Kitomari', 'Munisi', 'Materu', 'Shayo',
    ];

    public const OCCUPATIONS = [
        'Farmer', 'Teacher', 'Businessman', 'Businesswoman', 'Nurse', 'Driver',
        'Shopkeeper', 'Civil Servant', 'Tailor', 'Mechanic', 'Carpenter',
        'Accountant', 'Police Officer', 'Trader',
    ];

    public const WARDS = [
        'Moshi Urban', 'Hai', 'Siha', 'Rombo', 'Mwanga', 'Same', 'Arusha City',
        'Meru', 'Karatu', 'Monduli',
    ];

    /** title, author, category */
    public const BOOKS = [
        ['Basic Mathematics Form 1-4',      'TIE',                    'Textbooks'],
        ['Physics for Secondary Schools',   'Oxford',                 'Textbooks'],
        ['Chemistry Practical Handbook',    'TIE',                    'Textbooks'],
        ['Biology Form 3-4',                'Longhorn',               'Textbooks'],
        ['Kiswahili Kidato cha 1-4',        'TIE',                    'Textbooks'],
        ['English Grammar in Use',          'Raymond Murphy',         'Textbooks'],
        ['Historia ya Tanzania',            'M. Ndulu',               'Textbooks'],
        ['Geography of East Africa',        'Macmillan',              'Textbooks'],
        ['Civics for Secondary Schools',    'TIE',                    'Textbooks'],
        ['Book-keeping Simplified',         'J. Mwakalinga',          'Textbooks'],
        ['Things Fall Apart',               'Chinua Achebe',          'Literature'],
        ['Shamba la Wanyama',               'George Orwell',          'Literature'],
        ['The River Between',               "Ngugi wa Thiong'o",      'Literature'],
        ['Passed Like a Shadow',            'B. Mapalala',            'Literature'],
        ['Kilio Chetu',                     'Medical Aid Foundation', 'Literature'],
        ['Oxford English Dictionary',       'Oxford',                 'Reference'],
        ['Kamusi ya Kiswahili Sanifu',      'TUKI',                   'Reference'],
        ['World Atlas',                     'Collins',                'Reference'],
    ];

    /** name, category, unit, qty, min, unit cost (TZS) */
    public const INVENTORY = [
        ['Student Desk (double)',   'Furniture',    'pcs',   240,  20,  85000],
        ['Teacher Table',           'Furniture',    'pcs',    28,   5, 145000],
        ['Plastic Chair',           'Furniture',    'pcs',   150,  20,  32000],
        ['Metal Bunk Bed',          'Furniture',    'pcs',    96,  10, 210000],
        ['Whiteboard Marker (box)', 'Stationery',   'box',    34,  15,  18000],
        ['Duplicating Paper A4',    'Stationery',   'ream',   62,  25,  14500],
        ['Exercise Book (48pg)',    'Stationery',   'pcs',  1200, 300,   1200],
        ['Chalk (box)',             'Stationery',   'box',     9,  12,   9500],
        ['Laboratory Beaker 250ml', 'Laboratory',   'pcs',    45,  15,  22000],
        ['Bunsen Burner',           'Laboratory',   'pcs',    18,   8,  78000],
        ['Microscope',              'Laboratory',   'pcs',     6,   4, 850000],
        ['Litmus Paper (pack)',     'Laboratory',   'pack',    7,  10,  15000],
        ['Football',                'Sports',       'pcs',    12,   6,  45000],
        ['Netball',                 'Sports',       'pcs',     8,   4,  38000],
        ['Maize (sack 100kg)',      'Kitchen',      'sack',   22,  10, 145000],
        ['Beans (sack 100kg)',      'Kitchen',      'sack',    9,  10, 280000],
        ['Cooking Oil (20L)',       'Kitchen',      'jerrycan', 6,   5, 115000],
        ['Broom',                   'Cleaning',     'pcs',    28,  15,   4500],
        ['Detergent (5L)',          'Cleaning',     'jerrycan', 11,  6,  28000],
        ['Toilet Paper (bale)',     'Cleaning',     'bale',   14,  10,  32000],
    ];

    /** name, icon */
    public const INVENTORY_CATEGORIES = [
        ['Furniture',  'fas fa-chair'],
        ['Stationery', 'fas fa-pen'],
        ['Laboratory', 'fas fa-flask'],
        ['Sports',     'fas fa-futbol'],
        ['Kitchen',    'fas fa-utensils'],
        ['Cleaning',   'fas fa-broom'],
    ];

    /** Fee items for the year, in TZS. title, amount, term */
    public const FEES = [
        ['School Fees - Term I',      380000, 1],
        ['School Fees - Term II',     380000, 2],
        ['Boarding Fee - Term I',     450000, 1],
        ['Boarding Fee - Term II',    450000, 2],
        ['Examination Fee',            65000, 1],
        ['Caution Money',              50000, 1],
    ];
}
