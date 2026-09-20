<?php
namespace App\Models;
interface InterfaceModel
{
    function getAll(): array;
    function getById(int | string $id): array;
    function create(array $datos): bool;
    function update(array $datos, int | string $id): bool;
    function delete(int | string $id): bool;
}
