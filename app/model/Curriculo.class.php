<?php

/**
 * Curriculo Active Record
 * @author  <your-name-here>
 */
class Curriculo extends TRecord
{
    const TABLENAME = 'curriculo';
    const PRIMARYKEY = 'id';
    const IDPOLICY =  'max'; // {max, serial}

    private static $escolaridade = array(
        'Fundamental - Incompleto',
        'Fundamental - Completo',
        'Médio - Incompleto',
        'Médio - Completo',
        'Superior - Incompleto',
        'Superior - Completo',
        'Pós-graduação - Incompleto',
        'Pós-graduação - Completo'
    );

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
        parent::addAttribute('email');
        parent::addAttribute('cargo');
        parent::addAttribute('escolaridade');
        parent::addAttribute('observacao');
        parent::addAttribute('arquivo');
        parent::addAttribute('data_hora');
        parent::addAttribute('ip');
        parent::addAttribute('data_hora_envio');
    }

    public static function getItemsEscolaridade()
    {
        return Curriculo::$escolaridade;
    }

    public static function getEscola($id)
    {
        return Curriculo::$escolaridade[$id];
    }

    static public function newFromEmail($email)
    {
        return Curriculo::where('email', '=', $email)->first();
    }
}
