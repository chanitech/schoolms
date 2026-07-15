{{--
    Type-ahead suggestions for item name inputs: attach with list="common-items".
    Merges the school's existing inventory item names with a curated list of
    common school supplies. Free typing still works — this is only a helper.
    Pass $inventoryItems (optional) to include current stock items.
--}}
@php
    $commonSchoolItems = [
        // Teaching & stationery
        'Chalk (white)', 'Chalk (coloured)', 'Whiteboard markers', 'Blackboard duster',
        'Exercise books', 'Ballpoint pens', 'Pencils', 'Rulers', 'Mathematical sets',
        'Manila cards', 'Flip chart paper', 'A4 printing paper (ream)', 'Printer ink / toner',
        'Stapler', 'Staples', 'Paper clips', 'Envelopes', 'File folders', 'Marker pens',
        // Cleaning & hygiene
        'Brooms', 'Mops', 'Buckets', 'Toilet paper', 'Bar soap', 'Liquid soap',
        'Detergent powder', 'Disinfectant', 'Dustbins', 'Rubber gloves',
        // Health
        'First aid kit refill', 'Bandages', 'Cotton wool', 'Antiseptic',
        // Sports
        'Footballs', 'Netballs', 'Volleyballs', 'Whistles', 'Sports jerseys',
        // Laboratory
        'Lab reagents', 'Test tubes', 'Beakers', 'Safety goggles',
        // Kitchen & boarding
        'Rice (sack)', 'Beans (sack)', 'Maize flour (sack)', 'Cooking oil', 'Sugar', 'Salt',
        'Firewood', 'Charcoal', 'Mattresses', 'Bed sheets', 'Mosquito nets',
        // Maintenance & electrical
        'Cement (bag)', 'Paint', 'Timber', 'Nails', 'Iron sheets', 'Padlocks', 'Hinges',
        'Light bulbs', 'Extension cables', 'Batteries',
    ];

    $suggestions = collect($commonSchoolItems)
        ->merge(($inventoryItems ?? collect())->pluck('name'))
        ->filter()->unique()->sort()->values();
@endphp
<datalist id="common-items">
    @foreach($suggestions as $suggestion)
        <option value="{{ $suggestion }}">
    @endforeach
</datalist>
