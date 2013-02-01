<?php

class Vendedor {

    public function post_save($vendedor) {

        if ($vendedor->idVendedor) {

            //update
            $sqlUpdateUsuario = "UPDATE usuarios SET nome=:nome,email=:email,login=:login,senha=:senha WHERE id=:idUsuario";
            $sqlUpdateVendedor = "UPDATE vendedores SET cpf=:cpf,matricula=:matricula,dataContratacao=:dataContratacao WHERE id=:idVendedor";

            try {

                DB::beginTransaction();

                $vendedor->dataContratacao = DB::dateToMySql($vendedor->dataContratacao);

                $stmtUsuario = DB::prepare($sqlUpdateUsuario);
                $stmtUsuario->bindParam("nome", $vendedor->nome);
                $stmtUsuario->bindParam("email", $vendedor->email);
                $stmtUsuario->bindParam("login", $vendedor->login);
                $stmtUsuario->bindParam("senha", $vendedor->senha);
                $stmtUsuario->bindParam("idUsuario", $vendedor->idUsuario);
                $stmtUsuario->execute();

                $stmtVendedor = DB::prepare($sqlUpdateVendedor);
                $stmtVendedor->bindParam("cpf", $vendedor->cpf);
                $stmtVendedor->bindParam("matricula", $vendedor->matricula);
                $stmtVendedor->bindParam("dataContratacao", $vendedor->dataContratacao);
                $stmtVendedor->bindParam("idVendedor", $vendedor->idVendedor);
                $stmtVendedor->execute();


                DB::commit();
            } catch (Exception $exc) {
                DB::rollBack();
                throw new Exception($exc->getMessage());
            }
        } else {

            //insert
            $sqlInsertUsuario = "INSERT INTO usuarios (nome,email,login,senha,tipo) VALUES (:nome,:email,:login,:senha,:tipo)";
            $sqlInsertVendedor = "INSERT INTO vendedores (cpf,matricula,ativo,idUsuario,dataContratacao) VALUES (:cpf,:matricula,:ativo,:idUsuario,:dataContratacao)";

            try {

                DB::beginTransaction();

                $vendedor->ativo = "1";
                $vendedor->tipo = "v";

                $vendedor->dataContratacao = DB::dateToMySql($vendedor->dataContratacao);

                $stmtUsuario = DB::prepare($sqlInsertUsuario);
                $stmtUsuario->bindParam("nome", $vendedor->nome);
                $stmtUsuario->bindParam("email", $vendedor->email);
                $stmtUsuario->bindParam("login", $vendedor->login);
                $stmtUsuario->bindParam("senha", $vendedor->senha);
                $stmtUsuario->bindParam("tipo", $vendedor->tipo);

                $stmtUsuario->execute();

                $vendedor->idUsuario = DB::lastInsertId();

                $stmtVendedor = DB::prepare($sqlInsertVendedor);
                $stmtVendedor->bindParam("cpf", $vendedor->cpf);
                $stmtVendedor->bindParam("matricula", $vendedor->matricula);
                $stmtVendedor->bindParam("ativo", $vendedor->ativo);
                $stmtVendedor->bindParam("idUsuario", $vendedor->idUsuario);
                $stmtVendedor->bindParam("dataContratacao", $vendedor->dataContratacao);
                $stmtVendedor->execute();

                $vendedor->id = DB::lastInsertId();

                DB::commit();
            } catch (Exception $exc) {
                DB::rollBack();
                throw new Exception($exc->getMessage());
            }

            return $vendedor;
        }
    }

    function get_list($id) {

        $sql = "SELECT v.id,u.nome,u.senha,u.email,u.login,v.cpf,v.matricula,DATE_FORMAT(v.dataContratacao, '%d/%m/%Y') AS dataContratacao,u.id as idUsuario FROM usuarios u, vendedores v WHERE u.id=v.idUsuario AND v.id=:id";
        $stmt = DB::prepare($sql);
        $stmt->bindParam("id", $id);
        $stmt->execute();
        return($stmt->fetch());
    }

    function get_listAll($parameter) {

        $filtroWHERE = "";

        if ($parameter)
            $filtroWHERE = " AND u.nome LIKE '%{$parameter}%'";

        $sql = "SELECT v.id,u.nome,u.email,u.login,v.cpf,v.matricula,DATE_FORMAT(v.dataContratacao, '%d/%m/%Y') AS dataContratacao,u.id as idUsuario FROM usuarios u, vendedores v WHERE u.id=v.idUsuario $filtroWHERE";

        $stmt = DB::prepare($sql);
        $stmt->execute();

        $result = $stmt->fetchAll();
        return $result;
    }

    function post_delete($vendedor) {
        $sqlDeleteVendedor = "DELETE FROM vendedores WHERE id=:id";
        $sqlDeleteUsuario = "DELETE FROM usuarios WHERE id=:idUsuario";

        try {

            DB::beginTransaction();

            $stmt = DB::prepare($sqlDeleteVendedor);
            $stmt->bindParam("id", $vendedor->id);
            $stmt->execute();

            $stmt = DB::prepare($sqlDeleteUsuario);
            $stmt->bindParam("idUsuario", $vendedor->idUsuario);
            $stmt->execute();

            DB::commit();
        } catch (Exception $exc) {
            DB::rollBack();
            throw new Exception($exc->getMessage());
        }
    }

}