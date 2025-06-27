- Laravel 12 with Livewire and Tailwind v4

- Models are always globally unguarded, never create the `$fillable` property
- use `#Scope` attributes instead of the old `scope$Name` way, these HAVE to be protected!
- only add comments to complex code, not every line
- always import classes instead of referencing the full class path
- always write tests, use PestPHP
- always prefer `$model->update([...])` over `$model->property = 'foobar'; $model->save()`
- run `composer test` after changes to make sure tests aren't failing
