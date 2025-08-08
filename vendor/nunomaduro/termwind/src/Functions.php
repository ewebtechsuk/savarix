<?php
namespace Termwind;
function render(string $html): void
{
    echo strip_tags($html);
}
