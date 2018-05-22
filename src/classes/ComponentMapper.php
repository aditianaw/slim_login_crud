<?php

class ComponentMapper extends Mapper
{
    public function getComponents() {
        $sql = "SELECT id, component
            from components";
        $stmt = $this->db->query($sql);

        $results = [];
        while($row = $stmt->fetch()) {
            $results[] = new ComponentEntity($row);
        }
        return $results;
    }

    public function getComponentById($component_id) {
        $sql = "SELECT id, component
            from components where id = :component_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["component_id" => $component_id]);

        return new ComponentEntity($stmt->fetch());
    }
	
	
	
	  public function save(ComponentEntity $component) {
        $sql = "insert into components
            (id, component) values
            (:id, :component)";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "id" => $component->getId(),
            "component" => $component->getName(),
        ]);

        if(!$result) {
            throw new Exception("could not save record");
        }
    }
	
	
	public function update(ComponentEntity $component) {
        $sql = "update components set id=:id where id = :id";
        $stmt = $this->db->prepare($sql);
		$result = $stmt->execute([
				"id" =>$component->getId(),
				
		]); 
	 if(!$result) {
				throw new Exception("could not save record");
			}		
    }
	
	public function delete(ComponentEntity $component) {
		 $sql = "DELETE FROM components WHERE id=:id";
		 
				 $stmt = $this->db->prepare($sql);
				 $result = $stmt->execute([
				 "id" => $component->getId(),
				 ]);
				$db = null;
		}
}
 
 /*try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("id", $id);
        $stmt->execute();
        $db = null;
 echo '{"error":{"text":"successfully! deleted Records"}}';
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}
}
*/
