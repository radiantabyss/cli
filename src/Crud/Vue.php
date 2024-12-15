<?php
namespace Lumi\CLI\Crud;

class Vue
{
    public static function run($folder_path, $namespace, $item_name, $url) {
        @mkdir($folder_path.'/Actions', 0777, true);
        @mkdir($folder_path.'/Forms', 0777, true);
        @mkdir($folder_path.'/Partials', 0777, true);

        self::EditAction($folder_path, $namespace, $item_name, $url);
        self::ListAction($folder_path, $namespace, $item_name, $url);
        self::NewAction($folder_path, $namespace, $item_name, $url);

        self::Form($folder_path, $namespace, $item_name, $url);

        self::Filters($folder_path, $namespace, $item_name, $url);
        self::Table($folder_path, $namespace, $item_name, $url);
    }

    private static function EditAction($folder_path, $namespace, $item_name, $url) {
        $item_name_plural = plural($item_name);

        ob_start();

echo <<<END
<script>
import Form from './../Forms/Form.vue';

export default {
    components: { Form },
    data() {
        return {
            item: false,
        }
    },
    methods: {
        async submit(fields) {
            await Request.post(`$url/update/\${this.\$route.params.id}`, fields, true);
            this.redirect();
        },

        redirect() {
            this.\$router.push(localStorage.getItem('_previous_route') || `$url/single/\${this.\$route.params.id}`);
        },
    },
    async mounted() {
        let data = await Request.get(`$url/edit/\${this.\$route.params.id}`);
        this.item = data.item;
    },
}
</script>

<template>

<div v-if="item">
    <div class="page-title">
        <div>
            <t>Edit $item_name</t> #{{ item.id }}
            <div class="crumbs">
                <router-link to="/"><sprite id="home" /></router-link>
                <sprite id="arrow-right" />
                <router-link to="$url"><t>$item_name_plural</t></router-link>
                <sprite id="arrow-right" />
                <span><t>Edit $item_name</t> #{{ item.id }}</span>
            </div>
        </div>
    </div>

    <Form :item="item"
        @submit="submit"
        @cancel="redirect"
        v-if="item !== false"
    />
</div>
</template>
END;

        $contents = ob_get_clean();
        file_put_contents($folder_path.'/Actions/EditAction.vue', $contents);
    }

    private static function ListAction($folder_path, $namespace, $item_name, $url) {
        $item_name_plural = plural($item_name);

        ob_start();

echo <<<END
<script>
import Filters from './../Partials/Filters.vue';
import Table from './../Partials/Table.vue';

export default {
    components: { Filters, Table },
}
</script>

<template>
<div>
    <div class="page-title">
        <div>
            <t>$item_name_plural</t>
            <div class="crumbs">
                <router-link to="/"><sprite id="home" /></router-link>
                <sprite id="arrow-right" />
                <span>$item_name_plural</t></span>
            </div>
        </div>
        <router-link to="$url/new" class="btn btn--auto btn--small">
            <sprite id="plus" /> <t>New</t>
        </router-link>
    </div>

    <div class="panel">
        <div class="panel__title"><t>Filters</t></div>
        <Filters class="mb-20" />
    </div>

    <div class="panel">
        <Table :params="\$route.query" />
    </div>
</div>
</template>
END;

        $contents = ob_get_clean();
        file_put_contents($folder_path.'/Actions/ListAction.vue', $contents);
    }

    private static function NewAction($folder_path, $namespace, $item_name, $url) {
        $item_name_plural = plural($item_name);

        ob_start();

echo <<<END
<script>
import Form from './../Forms/Form.vue';

export default {
    components: { Form },
    data() {
        return {
            item: false,
        }
    },
    methods: {
        async submit(fields) {
            await Request.post('$url/create', fields, true);
            this.redirect();
        },

        redirect() {
            this.\$router.push(localStorage.getItem('_previous_route') || '$url');
        },
    },
    async mounted() {
        if ( this.\$route.params.id ) {
            let data = await Request.get(`$url/edit/\${this.\$route.params.id}`);
            this.item = data.item;
        }
        else {
            this.item = null;
        }
    },
}
</script>

<template>

<div>
    <div class="page-title">
        <div>
            <t>New $item_name</t>
            <div class="crumbs">
                <router-link to="/"><sprite id="home" /></router-link>
                <sprite id="arrow-right" />
                <router-link to="$url"><t>$item_name_plural</t></router-link>
                <sprite id="arrow-right" />
                <span><t>New $item_name</t></span>
            </div>
        </div>
    </div>

    <Form :item="item"
        @submit="submit"
        @cancel="redirect"
        v-if="item !== false"
    />
</div>
</template>
END;

        $contents = ob_get_clean();
        file_put_contents($folder_path.'/Actions/NewAction.vue', $contents);
    }

    private static function Form($folder_path, $namespace, $item_name, $url) {
        $item_name_plural = plural($item_name);

        ob_start();

echo <<<END
<script>
export default {
    name: 'Form',
    props: {
        item: {
            type: Object,
            required: false,
            default: null,
        },
        redirect: {
            type: Boolean,
            required: false,
            default: true,
        }
    },
    data() {
        return {
            fields: {
                ...(this.item || {})
            },
        }
    },
    methods: {
        submit(e) {
            this.\$emit('submit', {
                _event: e,
                ...this.fields,
            });
        },

        toggle(key) {
            this[key] = !this[key];
        },
    },
    mounted() {
        setTimeout(() => {
            this.\$refs.form.getElementsByTagName('input')[0].focus();
        }, 150);
    },
}
</script>

<template>
<form ref="form">
    <div class="panel panel--50">
        <div class="row">
            <label><t>Name</t></label>
            <input type="text" class="input" v-model="fields.name" />
        </div>
    </div>

    <div class="panel panel--50">
        <div class="row row--submit">
            <button type="submit" @click.prevent="submit" class="btn btn--medium"><t>Save</t></button>
            <a @click="\$emit('cancel')"><sprite id="cancel" /><t> Cancel</t></a>
        </div>
    </div>
</form>
</template>
END;

        $contents = ob_get_clean();
        file_put_contents($folder_path.'/Forms/Form.vue', $contents);
    }

    private static function Filters($folder_path, $namespace, $item_name, $url) {
        $item_name_plural = plural($item_name);

        ob_start();

echo <<<END
<script>
export default {
    name: 'Filters',
    mixins: [Mixins.Filters],
    data() {
        return {
            fields: {},
        }
    },
}
</script>

<template>
<div class="rows rows--filters">
    <a @click="clear" class="btn btn--small btn--outline clear-filters"><t>Clear Filters</t></a>

    <div class="row">
        <label><t>Name</t></label>
        <clearable v-model="fields.name" />
    </div>
</div>
</template>
END;

        $contents = ob_get_clean();
        file_put_contents($folder_path.'/Partials/Filters.vue', $contents);
    }

    private static function Table($folder_path, $namespace, $item_name, $url) {
        $item_name_plural = plural($item_name);

        ob_start();

echo <<<END
<script>
export default {
    name: 'Table',
    props: {
        params: {
            type: Object,
            required: false,
            default() {
                return {};
            }
        },
    },
    data() {
        return {
            items: false,
            total: false,
            pages: false,
            loading: true,
        }
    },
    methods: {
        async mount() {
            this.loading = true;
            let data = await Request.get('$url', this.params);

            this.items = data.items;
            this.total = data.total;
            this.pages = data.pages;
            this.loading = false;
        },

        async deleteItem(id) {
            await Confirm({
                question: __('Are you sure you want to delete this $item_name?'),
            });

            await Request.get(`$url/delete/\${id}`);
            this.items = Items.delete(this.items, id);
        },
    },
    mounted() {
        this.mount();
    },
    watch: {
        params() {
            this.mount();
        }
    }
}
</script>

<template>
<div class="table-wrapper">
    <div class="loading-overlay" v-if="loading"><sprite id="request-spinner" /></div>

    <template v-if="items !== false">
        <template v-if="items.length">
            <pagination :pages="pages" :total="total" />
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 40px;"></th>
                        <th-sort style="width: 40px;"><t>ID</t></th-sort>
                        <th-sort field="name"><t>Name</t></th-sort>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in items" :key="item.id">
                        <td>
                            <miniburger>
                                <router-link :to="`$url/edit/\${item.id}`">
                                    <sprite id="edit" /> <t>Edit</t>
                                </router-link>
                                <a @click="deleteItem(item.id)" class="color-red">
                                    <sprite id="trash" /> <t>Delete</t>
                                </a>
                            </miniburger>
                        </td>
                        <td>{{ item.id }}</td>
                        <td>{{ item.name }}</td>
                    </tr>
                </tbody>
            </table>
            <pagination :pages="pages" :total="total" />
        </template>
        <template v-else>
            <t>No $item_name found.</t>
        </template>
    </template>
</div>
</template>
END;

        $contents = ob_get_clean();
        file_put_contents($folder_path.'/Partials/Table.vue', $contents);
    }
}
