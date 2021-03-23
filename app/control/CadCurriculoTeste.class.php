<?php

use Adianti\Validator\TEmailValidator;

/**
 * CurriculoForm Form
 * @author  <your name here>
 */
class CadCurriculoTeste extends TPage
{
    protected $form; // form

    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct($param)
    {
        parent::__construct();


        // creates the form
        $this->form = new BootstrapFormBuilder('form_Curriculo');
        $this->form->setFormTitle('Curriculo');


        // create the form fields
        $id = new TEntry('id');
        $nome = new TEntry('nome');
        $email = new TEntry('email');
        $cargo = new TEntry('cargo');
        $escolaridade = new TCombo('escolaridade');
        $escolaridade->addItems(Curriculo::getItemsEscolaridade());
        $observacao = new TEntry('observacao');
        $arquivo = new TFile('arquivo');
        $arquivo->setAllowedExtensions(['doc', 'docx', 'pdf']);
        $arquivo->setTamanho(1);


        // add the fields
        $this->form->addFields([new TLabel('Nome:')], [$nome]);
        $this->form->addFields([new TLabel('E-mail:')], [$email]);
        $this->form->addFields([new TLabel('Cargo:')], [$cargo]);
        $this->form->addFields([new TLabel('Escolaridade')], [$escolaridade]);
        $this->form->addFields([new TLabel('Observações:')], [$observacao]);
        $this->form->addFields([new TLabel('Arquivo')], [$arquivo]);

        // set sizes
        $id->setSize('100%');
        $nome->setSize('100%');
        $email->setSize('100%');
        $cargo->setSize('100%');
        $escolaridade->setSize('100%');
        $observacao->setSize('100%');
        $arquivo->setSize('100%');

        if (!empty($id)) {
            $id->setEditable(FALSE);
        }

        $nome->addValidation('Nome', new TRequiredValidator);
        $email->addValidation('E-mail', new TRequiredValidator);
        $email->addValidation('E-mail', new TEmailValidator);
        $cargo->addValidation('Cargo', new TRequiredValidator);
        $escolaridade->addValidation('Escolaridade', new TRequiredValidator);
        $arquivo->addValidation('Arquivo', new TRequiredValidator);

        // create the form actions
        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'),  new TAction([$this, 'onEdit']), 'fa:eraser red');

        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);

        parent::add($container);
    }

    /**
     * Save form data
     * @param $param Request
     */
    public function onSave($param)
    {
        try {
            TTransaction::open('sample'); // open a transaction

            $this->form->validate(); // validate form data

            if (Curriculo::newFromEmail($param['email']) instanceof Curriculo) {
                throw new Exception('E-mail já registrado.');
                return;
            }

            $data = $this->form->getData(); // get form data as array

            $object = new Curriculo;  // create an empty object
            $object->fromArray((array) $data); // load the object with data
            $object->ip = $_SERVER['REMOTE_ADDR'];
            $object->data_hora = date('d/m/Y H:i:s');
            $object->data_hora_envio = date('d/m/Y H:i:s');
            $object->store(); // save the object

            $this->onEnvEmail($param);

            $this->form->clear();
            TTransaction::close(); // close the transaction

            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'));
        } catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            $this->form->setData($this->form->getData()); // keep form data
            TTransaction::rollback(); // undo all pending operations
        }
    }

    /**
     * Clear form data
     * @param $param Request
     */
    public function onClear($param)
    {
        $this->form->clear(TRUE);
    }

    /**
     * Load object to form data
     * @param $param Request
     */
    public function onEdit($param)
    {
        try {
            if (isset($param['key'])) {
                $key = $param['key'];  // get the parameter $key
                TTransaction::open('sample'); // open a transaction
                $object = new Curriculo($key); // instantiates the Active Record
                $this->form->setData($object); // fill the form
                TTransaction::close(); // close the transaction
            } else {
                $this->form->clear(TRUE);
            }
        } catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }

    public function onEnvEmail($param)
    {
        try {
            $replaces = [];
            $replaces['nome']  = $param['nome'];
            $replaces['email'] = $param['email'];
            $replaces['cargo'] = $param['cargo'];
            $replaces['escol'] = Curriculo::getEscola($param['escolaridade']);
            $replaces['arquivo'] = $param['arquivo'];

            $html = new THtmlRenderer('app/resources/email.html');
            $html->enableSection('main', $replaces);

            MailService::send($param['email'], 'Dados Formulário', $html->getContents(), 'html');
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}
