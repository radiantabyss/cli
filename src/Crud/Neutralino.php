<?php
namespace RA\CLI\Crud;

class Neutralino
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
import Validator from './../Validators/Validator.js';
import Transformer from './../Transformers/Transformer.js';
import Presenter from './../Presenters/Presenter.js';
let self = {
    async run() {
        let data = Invoke.all();

        //validate request
        let validation = await Validator.run(data);
        if ( validation !== true ) {
            return Response.error(validation);
        }

        data = await Transformer.run(data);
        let item = await Model.$model_name.create(data);
        item = Presenter.run(item);

        return Response.success({ item });
    },
}

export default self;
END;

        $contents = ob_get_clean();
        abs_file_put_contents($folder_path.'/Actions/CreateAction.js', $contents);
    }

    private static function DeleteAction($folder_path, $namespace, $model_name, $item_name) {
        ob_start();

echo <<<END
let self = {
    async run(id) {
        let item = await Model.$model_name.find(id);
        if ( !item ) {
            return Response.error('$item_name not found.');
        }

        Model.Panel.destroy({
            where: { id }
        });

        return Response.success();
    },
}

export default self;
END;

        $contents = ob_get_clean();
        abs_file_put_contents($folder_path.'/Actions/DeleteAction.js', $contents);
    }

    private static function EditAction($folder_path, $namespace, $model_name, $item_name) {
        ob_start();

echo <<<END
import EditPresenter from './../Presenters/EditPresenter.js';

let self = {
    async run(id) {
        let item = await Model.$model_name.find(id);
        if ( !item ) {
            return Response.error('$item_name not found.');
        }

        item = EditPresenter.run(item);
        return Response.success({ item });
    },
}

export default self;
END;

        $contents = ob_get_clean();
        abs_file_put_contents($folder_path.'/Actions/EditAction.js', $contents);
    }

    private static function ListAction($folder_path, $namespace, $model_name, $item_name) {
        ob_start();

echo <<<END
import ListPresenter from './../Presenters/ListPresenter.js';

let self = {
    async run() {
        let items = await Model.$model_name.findAll();
        items = ListPresenter.run(items);

        return Response.success({ items });
    },
}

export default self;
END;

        $contents = ob_get_clean();
        abs_file_put_contents($folder_path.'/Actions/ListAction.js', $contents);
    }

    private static function PatchAction($folder_path, $namespace, $model_name, $item_name) {
        ob_start();

echo <<<END
import PatchValidator from './../Validators/PatchValidator.js';
import PatchTransformer from './../Transformers/PatchTransformer.js';
import Presenter from './../Presenters/Presenter.js';

let self = {
    async run(id) {
        let data = Invoke.all();

        //validate request
        let validation = await PatchValidator.run(data);
        if ( validation !== true ) {
            return Response.error(validation);
        }

        data = await PatchTransformer.run(data, id);
        await Model.$model_name.update(data, {
            where: { id }
        });

        let item = await Model.$model_name.find(id);
        item = Presenter.run(item);

        return Response.success({ item });
    }
}
END;

        $contents = ob_get_clean();
        abs_file_put_contents($folder_path.'/Actions/PatchAction.js', $contents);
    }

    private static function SearchAction($folder_path, $namespace, $model_name, $item_name) {
        ob_start();

echo <<<END
let self = {
    async run() {

    },
};

export default self;
END;

        $contents = ob_get_clean();
        abs_file_put_contents($folder_path.'/Actions/SearchAction.js', $contents);
    }

    private static function SingleAction($folder_path, $namespace, $model_name, $item_name) {
        ob_start();

echo <<<END
import Presenter from './../Presenters/Presenter.js';

let self = {
    async run(id) {
        let item = await Model.$model_name.find(id);
        if ( !item ) {
            return Response.error('$item_name not found.');
        }

        item = Presenter.run(item);
        return Response.success({ item });
    },
}

export default self;
END;

        $contents = ob_get_clean();
        abs_file_put_contents($folder_path.'/Actions/SingleAction.js', $contents);
    }

    private static function UpdateAction($folder_path, $namespace, $model_name, $item_name) {
        ob_start();

echo <<<END
import Validator from './../Validators/Validator.js';
import Transformer from './../Transformers/Transformer.js';
import Presenter from './../Presenters/Presenter.js';

let self = {
    async run(id) {
        let data = Invoke.all();

        //validate request
        let validation = await Validator.run(data);
        if ( validation !== true ) {
            return Response.error(validation);
        }

        data = await Transformer.run(data, id);
        await Model.$model_name.update(data, {
            where: { id }
        });

        let item = await Model.$model_name.find(id);
        item = Presenter.run(item);

        return Response.success({ item });
    },
}

export default self;
END;

        $contents = ob_get_clean();
        abs_file_put_contents($folder_path.'/Actions/UpdateAction.js', $contents);
    }

    private static function Filter($folder_path, $namespace, $model_name, $item_name) {
        $model_name = snake_case($model_name);
        ob_start();

echo <<<END
let self = {
    run() {

    },
};

export default self;
END;

        $contents = ob_get_clean();
        abs_file_put_contents($folder_path.'/Filters/Filter.js', $contents);
    }

    private static function EditPresenter($folder_path, $namespace, $model_name, $item_name) {
        ob_start();

echo <<<END
<?php
let self = {
    run(item) {
        delete item.created_at;
        delete item.updated_at;

        return item;
    },
}
END;

        $contents = ob_get_clean();
        abs_file_put_contents($folder_path.'/Presenters/EditPresenter.js', $contents);
    }

    private static function ListPresenter($folder_path, $namespace, $model_name, $item_name) {
        ob_start();

echo <<<END
<?php
let self = {
    run(items) {
        return items;
    },
}

export default self;
END;

        $contents = ob_get_clean();
        abs_file_put_contents($folder_path.'/Presenters/ListPresenter.js', $contents);
    }

    private static function Presenter($folder_path, $namespace, $model_name, $item_name) {
        ob_start();

echo <<<END
let self = {
    run(item) {
        return item;
    },
}

export default self;
END;

        $contents = ob_get_clean();
        abs_file_put_contents($folder_path.'/Presenters/Presenter.js', $contents);
    }

    private static function PatchTransformer($folder_path, $namespace, $model_name, $item_name) {
        ob_start();

echo <<<END
let self = {
    allowed_fields: [],

    run(data) {
        for ( let key in data ) {
            if ( !self.allowed_fields.includes(key) ) {
                delete data[key];
            }
        }

        return data;
    },
}

export default self;
END;

        $contents = ob_get_clean();
        abs_file_put_contents($folder_path.'/Transformers/PatchTransformer.js', $contents);
    }

    private static function Transformer($folder_path, $namespace, $model_name, $item_name) {
        ob_start();

echo <<<END
let self = {
    run(data, id = null) {
        delete data.id;
        return data;
    },
};

export default self;
END;

        $contents = ob_get_clean();
        abs_file_put_contents($folder_path.'/Transformers/Transformer.js', $contents);
    }

    private static function PatchValidator($folder_path, $namespace, $model_name, $item_name) {
        ob_start();

echo <<<END
let self = {
    run(data) {
        return true;
    },
};

export default self;
END;

        $contents = ob_get_clean();
        abs_file_put_contents($folder_path.'/Validators/PatchValidator.js', $contents);
    }

    private static function Validator($folder_path, $namespace, $model_name, $item_name) {
        ob_start();

echo <<<END
let self = {
    async run(data, id = null) {
        //check if item exists
        if ( id ) {
            let item = await Model.$model_name.find();
            if ( !item ) {
                return `$item_name not found.`;
            }
        }

        let validation = Validator.make(data, {
            'name': 'required',
        }, {
            'name.required': 'Name is required.',
        });

        if ( !validation.passes() ) {
            return validation.messages();
        }

        return true;
    },
}

export default self;
END;

        $contents = ob_get_clean();
        abs_file_put_contents($folder_path.'/Validators/PatchValidator.js', $contents);
    }
}
