<?php

namespace App\Admin\Controllers;

use App\CompanyBasicInfo;
use App\CompanyStatus;
use App\GroupCategory;
use Encore\Admin\Controllers\AdminController;
use Illuminate\Database\Eloquent\Collection;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
Use Encore\Admin\Widgets\Table;
use Grid\Displayers\Actions;

class CompanyBasicInfoController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '基本資料表暨輔導歷程';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CompanyBasicInfo());

        $grid->filter(function($filter){

            $_option = array();
            $GroupOptions = GroupCategory::all();
            foreach ($GroupOptions as $item) {
                $_option[$item->slug] = $item->name;
            }

            $filter->disableIdFilter();
            $filter->equal('group_category', '進駐單位')->select($_option);
            $filter->equal('real_or_virtula', '進駐方式')->select([
                'real' => '實質進駐',
                'virtual' => '虛擬進駐'
            ]);
            $filter->like('company_name', '自然人/組織/公司名稱');
            $filter->like('identity_code', '身分證/統一編號');
            $filter->where(function ($query) {
                $query->where('contact_name', 'like', "%{$this->input}%")
                    ->orWhere('owner_name', 'like', "%{$this->input}%");
            }, '聯絡人/負責人姓名');
            $filter->where(function ($query) {
                $query->where('contact_email', 'like', "%{$this->input}%")
                    ->orWhere('owner_email', 'like', "%{$this->input}%");
            }, '聯絡人/負責人Email');
            $filter->where(function ($query) {
                $query->where('contact_phone', 'like', "%{$this->input}%")
                    ->orWhere('owner_phone', 'like', "%{$this->input}%");
            }, '聯絡人/負責人電話');
        });

        $grid->export(function ($export) {
            $export->except(['tmp']);
            $export->column('company_name', function ($value, $original) {
                return $original;
            });
        });

        $grid->model()->collection(function (Collection $collection) {
            foreach($collection as $index => $item) {
                $item->tmp = $index + 1;
            }
            return $collection;
        });
        
        $grid->column('tmp', '編號');
        
        $grid->column('group_category', '進駐單位')->using([
            'farmer'        => '農試所',
            'forestry'      => '林試所',
            'water'         => '水試所',
            'livestock'     => '畜試所',
            'agricultural'  => '農科院',
        ], '未知');
        
        // $grid->column('group_category', '進駐單位')->display(function($slug){
        //     $reault = GroupCategory::where('slug', $slug)->first()->name;
        //     return '<span class="badge badge-primary" style="background:blue">'.$reault.'</span>';
        // });
        
        #暫時先不使用彈出效果，因會影響前端Excel輸出故關閉
        /*
        $grid->column('company_name', '自然人/組織/公司名稱')->expand(function ($model) {

            $row = CompanyBasicInfo::where('id', $this->id)->get()->map(function ($row) {
                return $row->only([
                    'owner_name', 
                    'owner_email', 
                    'owner_phone',
                    'project_name',
                    'service',
                    'contract_time',
                    'capital_checkin',
                    'revenue_checkin',
                    'staff_checkin'
                ]);
            });
        
            return new Table([
                '負責人', 
                '負責人Email',
                '負責人電話',
                '營運專案名稱',
                '主要產品/服務項目',
                '合約日期',
                '進駐時實收資本額',
                '進駐時年營業額',
                '進駐時員工人數'
                ], $row->toArray());
        });
        */
        $grid->column('company_name', '自然人/組織/公司名稱')->display(function($company_name){
            return "<a target='_blank' href=/company-info-view/".$this->cid.">".$company_name."</a>";
        });
        $grid->column('identity_code', '身分證/統一編號');
        $grid->column('established_time', '設立日期')->display(function($established_time){
            return date("Y-m-d", strtotime($established_time));  
        });
        $grid->column('real_or_virtula', '進駐方式')->using([
            'real'      => '實質進駐', 
            'virtual'    => '虛擬進駐'
        ]);
        $grid->column('contact_name', '聯絡人');
        $grid->column('contact_email', '聯絡人Email');
        $grid->column('contact_phone', '聯絡人電話');
        $grid->column('owner_name', __('負責人'));
        $grid->column('owner_email', __('負責人Email'));
        $grid->column('owner_phone', __('負責人電話'));
        $grid->column('project_name', __('營運專案名稱'));
        $grid->column('service', __('主要產品/服務項目'));
        $grid->column('contract_start_time', __('合約開始日期'))->display(function($contract_start_time){
            return date("Y-m-d", strtotime($contract_start_time));  
        });
        $grid->column('contract_end_time', __('合約結束日期'))->display(function($contract_end_time){
            return date("Y-m-d", strtotime($contract_end_time));  
        });
        $grid->column('capital', __('進駐時實收資本額'));
        $grid->column('revenue', __('進駐時年營業額'));
        $grid->column('staff', __('進駐時員工人數'));

        // $grid->column('created_at', __('Created at'));
        // $grid->column('updated_at', __('Updated at'));

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(CompanyBasicInfo::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('group_category', __('Group category'));
        $show->field('company_name', __('Company name'));
        $show->field('identity_code', __('Identity code'));
        $show->field('established_time', __('Established time'));
        $show->field('contact_name', __('Contact name'));
        $show->field('contact_email', __('Contact email'));
        $show->field('contact_phone', __('Contact phone'));
        $show->field('owner_name', __('Owner name'));
        $show->field('owner_email', __('Owner email'));
        $show->field('owner_phone', __('Owner phone'));
        $show->field('project_name', __('Project name'));
        $show->field('service', __('Service'));
        $show->field('contract_time', __('Contract time'));
        $show->field('capital', __('Capital'));
        $show->field('revenue', __('Revenue'));
        $show->field('staff', __('Staff'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new CompanyBasicInfo());

        $form->column(1/2, function ($form) {

            $_groupOption = array();
            $GroupOptions = GroupCategory::all();
            foreach ($GroupOptions as $item) {
                $_groupOption[$item->slug] = $item->name;
            }

            $form->hidden('cid')->default(uniqid());
            $form->select('group_category','進駐單位')->options($_groupOption);
            $form->text('company_name', '自然人/組織/公司名稱');
            $form->text('contact_name', '聯絡人');
            $form->text('contact_email', '聯絡人Email');
            $form->text('contact_phone', '聯絡人電話');
            $form->text('project_name', '營運專案名稱');
            $form->number('capital', '進駐時實收資本額');
            $form->number('revenue', '進駐時年營業額');
            $form->number('staff', '進駐時員工人數');

        });

        $form->column(1/2, function ($form) {
            $form->select('real_or_virtula','進駐方式')->options([
                'real' => '實質進駐',
                'virtual' => '虛擬進駐'
            ]);

            $form->text('identity_code', '身分證/統一編號');
            $form->text('owner_name', '負責人');
            $form->text('owner_email', '負責人Email');
            $form->text('owner_phone', '負責人電話');
            $form->text('service', '主要產品/服務項目');
            $form->datetime('established_time', '設立日期')->default(date('Y-m-d'));
            $form->datetime('contract_start_time', '合約開始日期')->default(date('Y-m-d'));
            $form->datetime('contract_end_time', '合約結束日期')->default(date('Y-m-d'));
        });

        $form->saving(function (Form $form) {
            $companyStatus = new CompanyStatus();
            $companyStatus->cid = $form->cid;
            $companyStatus->status = 'stationed';
            $companyStatus->note = '初次進駐';
            $companyStatus->date_time = $form->contract_start_time;
            $companyStatus->save();
        });

        return $form;
    }
}
