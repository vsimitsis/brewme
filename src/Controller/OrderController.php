<?php

namespace BrewMe\Controller;

class OrderController {

    /**
     * The available commands
     */
    const commands = [
        'prepare',
        'grab',
        'set',
        'help'
    ];

    /**
     * The user arguments
     *
     * @var array
     */
    protected $args = [];

    public function post()
    {
        //Validate the post and grab the arguments
        $this->validateInput();

        switch ($this->args[0]) {
            case '-help':
                return $this->getHelp();
                break;
            case 'order':
                return $this->storeOrder();
                break;
            default:
                return $this->getHelp();
        }
    }

    private function storeOrder()
    {
        return "Order";
    }

    /**
     * Validate the user's input and store the args
     */
    private function validateInput()
    {
        //Grab the post data
        $post = htmlspecialchars($_POST['text'], ENT_QUOTES);

        //Seperate arguments and make sure the command is an available command
        $this->args = explode(" ", $post);

        if (!isset($this->args[0])) {
            $msg = 'Welcome to BrewMe. Have a look at `brew help` for valid commands';
            return $this->error($msg);
        }
        else if (!in_array($this->args[0], $this->commands)) {
            $msg = $this.args[0] . ' is not a valid command. Have a look at `brew help` for valid commands';
            return $this->error($msg);
        }
    }

    /**
     * Returns the help command list
     *
     * @return false|string
     */
    private function getHelp()
    {
        $help = "
        Welcome to BrewMe!
        - The command format for brew ordering is `/brew prepare {brew}:{extra}-{extra}`
        Where `{brew}` Coffee or Tea
              `{extra}` Milk or Sugar
        - The command format for fridge ordering is `/brew grab {item}
        Where `{item]` is just an item from the fridge
        - The command format for ordering your default brew is `/brew order-default`
        - The command format for setting your default brew is `/brew set-default {brew}:{extra}-{extra}`
          Where `{brew}` Coffee or Tea
              `{extra}` Milk or Sugar
        ";
        return json_encode([
            'response_type' => "in_channel",
            "text" => "BrewMe Awesome Documentation",
            "attachments" => [
                [
                    "text" => $help
                ]
            ]
        ]);
    }

    /**
     * Returns an error response
     *
     * @param string $msg
     * @return false|string
     */
    private function error($msg = "There was a problem. Please try again or check `/brew help`")
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