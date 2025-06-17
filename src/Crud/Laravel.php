<?php
namespace RA\CLI\Crud;

class Laravel
{
    public static function run($folder_path, $namespace, $model_name, $item_name) {
        @mkdir($folder_path.'/Actions', 0777, true);
        @mkdir($folder_path.'/Filters', 0777, true);
        @mkdir($folder_path.'/Presenters', 0777, true);
        @mkdir($folder_path.'/Transformers', 0777, true);
        @mkdir($folder_path.'/Validators', 0777, true);

        self::CreateAction($folder_path, $namespace, $model_name, $item_name);
        self::DeleteAction($folder_path, $namespace, $model_name, $item_name);
        self::EditAction($folder_path, $namespace, $model_name, $item_name);
        self::ListAction($folder_path, $namespace, $model_name, $item_name);
        self::PatchAction($folder_path, $namespace, $model_name, $item_name);
        self::SearchAction($folder_path, $namespace, $model_name, $item_name);
        self::SingleAction($folder_path, $namespace, $model_name, $item_name);
        self::UpdateAction($folder_path, $namespace, $model_name, $item_name);

        self::Filter($folder_path, $namespace, $model_name, $item_name);

        self::EditPresenter($folder_path, $namespace, $model_name, $item_name);
        self::ListPresenter($folder_path, $namespace, $model_name, $item_name);
        self::Presenter($folder_path, $namespace, $model_name, $item_name);

        self::PatchTransformer($folder_path, $namespace, $model_name, $item_name);
        self::Transformer($folder_path, $namespace, $model_name, $item_name);

        self::PatchValidator($folder_path, $namespace, $model_name, $item_name);
        self::Validator($folder_path, $namespace, $model_name, $item_name);
    }

    private static function CreateAction($folder_path, $namespace, $model_name, $item_name) {
        ob_start();

echo <<<END
<?php
namespace App\Domains\\$namespace\Actions;

use Illuminate\Routing\Controller as Action;
use RA\Response;
use App\Models as Model;
use App\Domains\\$namespace\Presenters\Presenter;
use App\Domains\\$namespace\Transformers\Transformer;
use App\Domains\\$namespace\Validators\Validator;

class CreateAction extends Action
{
    public function run() {
        \$data = \Request::all();

        \$validation = Validator::run(\$data);
        if ( \$validation !== true ) {
            return Response::error(\$validation);
        }

        \$data = Transformer::run(\$data);
        \$item = Model\\$model_name::create(\$data);
        \$item = Presenter::run(\$item);

        return Response::success(compact('item'));
    }
}
END;

        $contents = ob_get_clean();
        abs_file_put_contents($folder_path.'/Actions/CreateAction.php', $contents);
    }

    private static function DeleteAction($folder_path, $namespace, $model_name, $item_name) {
        ob_start();

echo <<<END
<?php
namespace App\Domains\\$namespace\Actions;

use Illuminate\Routing\Controller as Action;
use RA\Response;
use App\Models as Model;

class DeleteAction extends Action
{
    public function run(\$id) {
        \$item = Model\\$model_name::find(\$id);

        if ( !\$item ) {
            return Response::error(__('$item_name not found.'));
        }

        \$item->delete();

        return Response::success();
    }
}
END;

        $contents = ob_get_clean();
        abs_file_put_contents($folder_path.'/Actions/DeleteAction.php', $contents);
    }

    private static function EditAction($folder_path, $namespace, $model_name, $item_name) {
        ob_start();

echo <<<END
<?php
namespace App\Domains\\$namespace\Actions;

use Illuminate\Routing\Controller as Action;
use RA\Response;
use App\Models as Model;
use App\Domains\\$namespace\Presenters\EditPresenter;

class EditAction extends Action
{
    public function run(\$id) {
        \$item = Model\\$model_name::find(\$id);

        if ( !\$item ) {
            return Response::error(__('$item_name not found.'));
        }

        \$item = EditPresenter::run(\$item);

        return Response::success(compact('item'));
    }
}
END;

        $contents = ob_get_clean();
        abs_file_put_contents($folder_path.'/Actions/EditAction.php', $contents);
    }

    private static function ListAction($folder_path, $namespace, $model_name, $item_name) {
        ob_start();

echo <<<END
<?php
namespace App\Domains\\$namespace\Actions;

use Illuminate\Routing\Controller as Action;
use RA\Response;
use App\Models as Model;
use App\Domains\\$namespace\Filters\Filter;
use App\Domains\\$namespace\Presenters\ListPresenter;

class ListAction extends Action
{
    public function run() {
        //get query
        \$query = Model\\$model_name::query();

        //apply filters
        \$filters = \Request::all();
        Filter::apply(\$query, \$filters);

        //paginate
        \$per_page = \Request::get('per_page') ?: config('settings.data_table_per_page');
        \$paginated = \$query->paginate(\$per_page);
        \$items = ListPresenter::run(\$paginated->items());
        \$total = \$paginated->total();
        \$pages = \$paginated->lastPage();

        return Response::success(compact('items', 'total', 'pages'));
    }
}
END;

        $contents = ob_get_clean();
        abs_file_put_contents($folder_path.'/Actions/ListAction.php', $contents);
    }

    private static function PatchAction($folder_path, $namespace, $model_name, $item_name) {
        ob_start();

echo <<<END
<?php
namespace App\Domains\\$namespace\Actions;

use Illuminate\Routing\Controller as Action;
use RA\Response;
use App\Models as Model;
use App\Domains\\$namespace\Presenters\Presenter;
use App\Domains\\$namespace\Transformers\PatchTransformer;
use App\Domains\\$namespace\Validators\PatchValidator;

class PatchAction extends Action
{
    public function run(\$id) {
        \$data = \Request::all();

        //validate request
        \$validation = PatchValidator::run(\$data);
        if ( \$validation !== true ) {
            return Response::error(\$validation);
        }

        \$data = PatchTransformer::run(\$data);
        Model\\$model_name::where('id', \$id)->update(\$data);

        \$item = Model\\$model_name::find(\$id);
        \$item = Presenter::run(\$item);

        return Response::success(compact('item'));
    }
}
END;

        $contents = ob_get_clean();
        abs_file_put_contents($folder_path.'/Actions/PatchAction.php', $contents);
    }

    private static function SearchAction($folder_path, $namespace, $model_name, $item_name) {
        ob_start();

echo <<<END
<?php
namespace App\Domains\\$namespace\Actions;

use Illuminate\Routing\Controller as Action;
use RA\Response;
use App\Models as Model;

class SearchAction extends Action
{
    public function run() {
        \$data = \Request::all();

        \$query = Model\\$model_name::where(function(\$query) use(\$data) {
                if ( isset(\$data['id']) && is_numeric(\$data['id']) ) {
                    \$query->where('id', \$data['id']);
                }
                else {
                    \$query->where('id', \$data['term'])
                        ->orWhere('name', 'LIKE', '%'.\$data['term'].'%');
                }
            })
            ->limit(\$data['limit']);

        \$data['order_by'] = \$data['order_by'] ?? 'id';
        \$data['order'] = \$data['order'] ?? 'asc';
        \$query->orderBy(\$data['order_by'], \$data['order']);

        \$items = \$query->get();

        \$items = \$items->map(function(\$item) {
            return [
                'text' => \$item->name,
                'value' => \$item->id,
            ];
        });

        return Response::success(compact('items'));
    }
}

END;

        $contents = ob_get_clean();
        abs_file_put_contents($folder_path.'/Actions/SearchAction.php', $contents);
    }

    private static function SingleAction($folder_path, $namespace, $model_name, $item_name) {
        ob_start();

echo <<<END
<?php
namespace App\Domains\\$namespace\Actions;

use Illuminate\Routing\Controller as Action;
use RA\Response;
use App\Models as Model;
use App\Domains\\$namespace\Presenter;

class SingleAction extends Action
{
    public function run(\$id) {
        \$item = Model\\$model_name::find(\$id);

        if ( !\$item ) {
            return Response::error(__('$item_name not found.'));
        }

        \$item = Presenter::run(\$item);

        return Response::success(compact('item'));
    }
}
END;

        $contents = ob_get_clean();
        abs_file_put_contents($folder_path.'/Actions/SingleAction.php', $contents);
    }

    private static function UpdateAction($folder_path, $namespace, $model_name, $item_name) {
        ob_start();

echo <<<END
<?php
namespace App\Domains\\$namespace\Actions;

use Illuminate\Routing\Controller as Action;
use RA\Response;
use App\Models as Model;
use App\Domains\\$namespace\Presenters\Presenter;
use App\Domains\\$namespace\Transformers\Transformer;
use App\Domains\\$namespace\Validators\Validator;

class UpdateAction extends Action
{
    public function run(\$id) {
        \$data = \Request::all();

        //validate request
        \$validation = Validator::run(\$data, \$id);
        if ( \$validation !== true ) {
            return Response::error(\$validation);
        }

        \$data = Transformer::run(\$data, \$id);
        Model\\$model_name::where('id', \$id)->update(\$data);

        \$item = Model\\$model_name::find(\$id);
        \$item = Presenter::run(\$item);

        return Response::success(compact('item'));
    }
}
END;

        $contents = ob_get_clean();
        abs_file_put_contents($folder_path.'/Actions/UpdateAction.php', $contents);
    }

    private static function Filter($folder_path, $namespace, $model_name, $item_name) {
        $model_name = snake_case($model_name);
        ob_start();

echo <<<END
<?php
namespace App\Domains\\$namespace\Filters;

use RA\Filter as RA_Filter;

class Filter extends RA_Filter
{
    protected static \$table = '$model_name';
}
END;

        $contents = ob_get_clean();
        abs_file_put_contents($folder_path.'/Filters/Filter.php', $contents);
    }

    private static function EditPresenter($folder_path, $namespace, $model_name, $item_name) {
        ob_start();

echo <<<END
<?php
namespace App\Domains\\$namespace\Presenters;

class EditPresenter
{
    public static function run(\$item) {
        unset(\$item->created_at);
        unset(\$item->updated_at);

        return \$item;
    }
}
END;

        $contents = ob_get_clean();
        abs_file_put_contents($folder_path.'/Presenters/EditPresenter.php', $contents);
    }

    private static function ListPresenter($folder_path, $namespace, $model_name, $item_name) {
        ob_start();

echo <<<END
<?php
namespace App\Domains\\$namespace\Presenters;

class ListPresenter
{
    public static function run(\$items) {
        return \$items;
    }
}
END;

        $contents = ob_get_clean();
        abs_file_put_contents($folder_path.'/Presenters/ListPresenter.php', $contents);
    }

    private static function Presenter($folder_path, $namespace, $model_name, $item_name) {
        ob_start();

echo <<<END
<?php
namespace App\Domains\\$namespace\Presenters;

class Presenter
{
    public static function run(\$item) {
        return \$item;
    }
}
END;

        $contents = ob_get_clean();
        abs_file_put_contents($folder_path.'/Presenters/Presenter.php', $contents);
    }

    private static function PatchTransformer($folder_path, $namespace, $model_name, $item_name) {
        ob_start();

echo <<<END
<?php
namespace App\Domains\\$namespace\Transformers;

class PatchTransformer
{
    private static \$allowed_fields = [];

    public static function run(\$data) {
        foreach ( \$data as \$key => \$value ) {
            if ( !in_array(\$key, self::\$allowed_fields) ) {
                unset(\$data[\$key]);
            }
        }

        return \$data;
    }
}
END;

        $contents = ob_get_clean();
        abs_file_put_contents($folder_path.'/Transformers/PatchTransformer.php', $contents);
    }

    private static function Transformer($folder_path, $namespace, $model_name, $item_name) {
        ob_start();

echo <<<END
<?php
namespace App\Domains\\$namespace\Transformers;

class Transformer
{
    public static function run(\$data) {
        return \$data;
    }
}
END;

        $contents = ob_get_clean();
        abs_file_put_contents($folder_path.'/Transformers/Transformer.php', $contents);
    }

    private static function PatchValidator($folder_path, $namespace, $model_name, $item_name) {
        ob_start();

echo <<<END
<?php
namespace App\Domains\\$namespace\Validators;

class PatchValidator
{
    public static function run(\$data) {
        return true;
    }
}
END;

        $contents = ob_get_clean();
        abs_file_put_contents($folder_path.'/Validators/PatchValidator.php', $contents);
    }

    private static function Validator($folder_path, $namespace, $model_name, $item_name) {
        ob_start();

echo <<<END
<?php
namespace App\Domains\\$namespace\Validators;

use App\\Models as Model;

class Validator
{
    public static function run(\$data, \$id = null) {
        //check if item exists
        if ( \$id ) {
            \$item = Model\\$model_name::find(\$id);
            if ( !\$item ) {
                return __('$item_name not found.');
            }
        }

        //validate request params
        \$validator = \Validator::make(\$data, [
            'name' => 'required',
        ], [
            'name' => __('Name is required'),
        ]);

        if ( \$validator->fails() ) {
            return \$validator->messages();
        }

        return true;
    }
}
END;

        $contents = ob_get_clean();
        abs_file_put_contents($folder_path.'/Validators/Validator.php', $contents);
    }
}
