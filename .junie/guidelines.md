- Laravel 12 with Livewire and Tailwind v4

- Models are always globally unguarded, never create the `$fillable` property
- use scope attributes instead of the old `scope$Name` way
- only add comments to complex code, not every line
- always import classes instead of referencing the full class path
- always write test, use PestPHP
- always prefer `$model->update([...])` over `$model->property = 'foobar'; $model->save()`
- run `composer prepush` after changes to make sure tests and phpstan aren't failing
