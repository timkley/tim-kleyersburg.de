** Project information **
- This project is built with Laravel 12 using Livewire and Tailwind v4 as well as the Flux UI kit
- the project requirements definition is located at `project.prd`
- use context7 to get up-to-date information about these frameworks when using them

** Rules **
- only add comments to really complex code, not every line
 
** Laravel specific rules **
- Models are always globally unguarded, never create the `$fillable` property
- use `#Scope` attributes instead of the old `scope$Name` way, make sure to mark the function as protected
- always import classes instead of referencing the full class path
- always write tests using PestPHP
- always prefer `$model->update([...])` over `$model->property = 'foobar'; $model->save()`
- run `composer test` after changes to make sure tests aren't failing
