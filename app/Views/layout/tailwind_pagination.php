<?php

/**
 * @var \CodeIgniter\Pager\PagerRenderer $pager
 */
$pager->setSurroundCount(2);
?>

<nav aria-label="Page navigation" class="flex">
    <ul class="flex items-center gap-1.5 whitespace-nowrap">

        <?php if ($pager->hasPreviousPage()) : ?>
            <li class="flex-shrink-0">
                <a href="<?= $pager->getFirst() ?>" aria-label="First" class="flex items-center justify-center h-10 w-10 rounded-xl border border-gray-200 text-sm font-bold text-gray-500 bg-white hover:bg-gray-50 transition-all shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.75 19.5l-7.5-7.5 7.5-7.5m-6 15L5.25 12l7.5-7.5" />
                    </svg>
                </a>
            </li>
            <li class="flex-shrink-0">
                <a href="<?= $pager->getPreviousPage() ?>" aria-label="Previous" class="flex items-center justify-center h-10 w-10 rounded-xl border border-gray-200 text-sm font-bold text-gray-500 bg-white hover:bg-gray-50 transition-all shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                    </svg>
                </a>
            </li>
        <?php endif ?>

        <?php foreach ($pager->links() as $link) : ?>
            <?php
            $baseClass = "flex items-center justify-center h-10 min-w-[40px] px-3 rounded-xl text-sm font-bold transition-all border ";
            $activeClass = "bg-blue-600 text-white border-blue-600 shadow-lg scale-105 pointer-events-none";
            $normalClass = "bg-white border-gray-200 text-gray-600 hover:bg-gray-50 shadow-sm";
            $finalClass = $baseClass . ($link['active'] ? $activeClass : $normalClass);
            ?>
            <li class="flex-shrink-0">
                <a href="<?= $link['uri'] ?>"
                    class="<?= $finalClass ?>"
                    <?= $link['active'] ? 'style="background-color: #2563eb; color: #ffffff; border-color: #2563eb;"' : '' ?>>
                    <?= $link['title'] ?>
                </a>
            </li>
        <?php endforeach ?>

        <?php if ($pager->hasNextPage()) : ?>
            <li class="flex-shrink-0">
                <a href="<?= $pager->getNextPage() ?>" aria-label="Next" class="flex items-center justify-center h-10 w-10 rounded-xl border border-gray-200 text-sm font-bold text-gray-500 bg-white hover:bg-gray-50 transition-all shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </a>
            </li>
            <li class="flex-shrink-0">
                <a href="<?= $pager->getLast() ?>" aria-label="Last" class="flex items-center justify-center h-10 w-10 rounded-xl border border-gray-200 text-sm font-bold text-gray-500 bg-white hover:bg-gray-50 transition-all shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 4.5l7.5 7.5 7.5 7.5m-6-15l7.5 7.5-7.5 7.5" />
                    </svg>
                </a>
            </li>
        <?php endif ?>
    </ul>
</nav>