



### calcul du temps

- une journée est aproximativement de 7.5 heures
- on saisie en heure avec une granularité de 0.5

### scripts

- `yarn encore dev`



### excel export

https://phpspreadsheet.readthedocs.io



$interval = $end->diff($start);

// total days
$days = $interval->days;

// create an iterateable period of date (P1D equates to 1 day)
$period = new DatePeriod($start, new DateInterval('P1D'), $end);

// best stored as array, so you can add more than one
$holidays = array('2012-09-07'); // ici faut une date de début et une date de fin... pour chaque holidays

foreach($period as $dt) {
    $curr = $dt->format('D');

    // substract if Saturday or Sunday
    if ($curr == 'Sat' || $curr == 'Sun') {
        $days--;
    }

    // (optional) for the updated question
    elseif (in_array($dt->format('Y-m-d'), $holidays)) {
        $days--;
    }
}