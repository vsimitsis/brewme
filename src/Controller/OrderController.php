<?php

namespace BrewMe\Controller;

class OrderController extends BaseController {

    /**
     * The available commands
     */
    private $commands = [
        'prepare',
        'grab',
        'set',
        'help'
    ];

    /**
     * The available brews
     */
    private $drinks = [
        'coffee',
        'tea'
    ];

    /**
     * The user arguments
     *
     * @var array
     */
    private $args;

    /**
     * User's comments for his order
     *
     * @var
     */
    private $comments;

    public function post()
    {
        //Validate the post and grab the arguments
        if ($this->validateInput()) {
            return $this->validateInput();
        }

        switch ($this->args[0]) {
            case 'prepare':
                return $this->storeOrder();
                break;
            case 'grab':
                return $this->storeOrder();
                break;
            case 'set':
                return $this->setOrder();
                break;
            case 'help':
                return $this->getHelp();
                break;
            default:
                return $this->getHelp();
        }
    }

    private function storeOrder()
    {
        print_r($this->comments);
        return "Getting Order";
    }

    private function setOrder()
    {
        return "Setting Order";
    }

    /**
     * Validate the user's input and store the args
     */
    private function validateInput()
    {
        //Grab the post data
        $post = htmlspecialchars($_POST['text'], ENT_QUOTES);

        //Seperate arguments and comments and make sure the command is an available command
        $this->args = explode(" ", $post);
        $this->comments = explode(":", $post);

        if (empty($this->args[0])) {
            $msg = 'Welcome to BrewMe. Type `/brew help` for the full list of all valid commands';
            return $this->respond($msg);
        }
        else if (!in_array($this->args[0], $this->commands)) {
            $msg = '`' . $this->args[0] . '` is not a valid command. Type `brew help` for the full list of valid commands';
            return $this->respond($msg);
        }
        else if (!in_array($this->args[1], $this->drinks)) {
            $msg = 'Sorry but we don\'t currently serve `' . $this->args[1] . '`. Type `brew help` for the full list of valid commands and drinks.';
            return $this->respond($msg);
        }
        return false;
    }

    /**
     * Returns the help command list
     *
     * @return false|string
     */
    private function getHelp()
    {
        $msg = "
        We need to write the help part
        ";
        return $this->respond($msg);
    }

    /**
     * Returns a respond
     *
     * @param string $msg
     * @return false|string
     */
    private function respond($msg = "There was a problem. Please try again or type `/brew help`")
    {
        return json_encode([
            'response_type' => "in_channel",
            "text" => "BrewMe Awesome Documentation",
            "attachments" => [
                [
                    "text" => $msg
                ]
            ]
        ]);
    }
}