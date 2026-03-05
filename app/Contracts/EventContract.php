<?php

namespace App\Contracts;

use App\Contracts\EventType as ContractsEventType;

class EventContract {
	
    public function __construct(
        public ContractsEventType $action_type, 
        public string $action_by,     
        public string $action,        
        public int $created_by,      
        public int $user_id           
    ) 
	{ 
	}

}
