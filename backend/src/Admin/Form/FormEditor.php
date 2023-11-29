<?php


namespace App\Admin\Form;


use Creonit\AdminBundle\Component\EditorComponent;
use App\Model\FormResultFieldQuery;
use Creonit\AdminBundle\Component\Request\ComponentRequest;
use Creonit\AdminBundle\Component\Response\ComponentResponse;
use Propel\Runtime\ActiveQuery\Criteria;

class FormEditor extends EditorComponent
{

	/**
	 * @entity Form
	 * @title Форма
	 *
	 * @field title {constraints: [NotBlank()]}
	 * @field code {constraints: [NotBlank()]}
	 * @field notification_email {constraints: [NotBlank()]}
	 * @field mailing_template_id:external {title: 'entity.getTitle()'}
     *
     * @action export() {
     *
     *   this.request('export', {form_id: this.query.key}, null, function(response){
     *      if(this.checkResponse(response)) {
     *            document.location.href = response.data.file;
     *         }
     *     }.bind(this));
     * }
	 *
	 * @template
	 *
	 * {{ title | text | group('Название') }}
     * {% filter row %}
	 * {{ form_title | text | group('Заголовок') | col(6) }}
     * {{ button_text | text | group('Текст на кнопке', {notice: 'по умолчанию "Отправить"'}) | col(6) }}
     * {% endfilter %}
	 * {{ code | text | group('Идентификатор', {notice: 'техническое поле, не менять'}) }}
	 * {{ success_text | textedit | group('Сообщение пользователю при успешной отправке') }}
	 *
	 * {% if _key %}
	 *      {{ component('Forms.FormFieldTable', {form_id: _key}) | group('Поля') }}
     *      {{ button('Выгрузить данные', {size: 'sm', icon: 'navicon'}) | action('export') }}
	 * {% else %}
	 *      {{ '<p>Сохраните форму, чтобы добавлять поля</p>' | raw | group('Поля') }}
	 * {% endif %}
	 *
	 */
    public function schema()
    {
        $this->setHandler('export', [$this, 'export']);
    }

    /**
     * @param ComponentRequest $request
     * @param ComponentResponse $response
     */
    public function export(ComponentRequest $request, ComponentResponse $response)
    {
        $listTemp = FormResultFieldQuery::create()
            ->filterByFormId($request->query->get('form_id'))
            ->find()
            ;

        $list = [];

        foreach ($listTemp as $item){

            $list[$item->getResultId()][] = $item->getValue();
            $list[$item->getResultId()][] = $item->getFormResult()->getCreatedAt()->format('m.d.Y');

        }


        $fp = fopen('uploads/test.csv', 'w');

        $first_line = array('Имя', 'Email', 'Дата');
        fputcsv($fp, $first_line,';');

        foreach ($list as $fields) {

            $fields = array_unique($fields); //удаляю лишнюю дату
            $date = $fields[1];
            $fields[1] = $fields[2];
            $fields[2] = $date;             // ставлю дату на последнее место

            fputcsv($fp, $fields,';');
        }
        fclose($fp);

        $response->data->add(['file' => '../../uploads/test.csv']);
    }
}
