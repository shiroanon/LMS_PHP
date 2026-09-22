<?php
return [
    'name' => 'RJIT Central Library',
    'fine_per_day' => (int)($_ENV['FINE_RATE_PER_DAY'] ?? 2),
    'loan_days' => (int)($_ENV['LOAN_PERIOD_DAYS'] ?? 14),
    'max_books' => (int)($_ENV['MAX_BOOKS_PER_STUDENT'] ?? 3),
];
