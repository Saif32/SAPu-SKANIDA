<?php

/*
 * The MIT License
 *
 * Copyright 2014 s4if.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * Model dari Guru
 *
 * @author s4if
 */

use Goodby\CSV\Import\Standard\Lexer;
use Goodby\CSV\Import\Standard\Interpreter;
use Goodby\CSV\Import\Standard\LexerConfig;

class MdlGuru extends Model {
    public $nip;
    public $nama;
    public $password;
    public $jenis_kelamin;
    
    public function fetch(){
        
        $query = $this->db->prepare("select * from guru where nip = ?");
        $query->bindValue(1, $this->nip);
        
        try{
		
            $query->execute();
            $data = $query->fetch();
            $this->nama = $data['nama'];
            $this->password = $data['password'];
            $this->jenis_kelamin = $data['jenis_kelamin'];
 
	}catch(PDOException $e){
            die($e->getMessage());
        }
    }
    
    public function fetchTable(){
        
        $query = $this->db->prepare("select * from guru");
        
        try{
		
            $query->execute();
            $data = $query->fetchAll();
            return $data;
            
	}catch(PDOException $e){
            
            die($e->getMessage());
            
        }
    }
    
    public function assign($data = []){
        
        $this->nama = $data['nama'];
        $this->password = $data['password'];
        $this->jenis_kelamin = $data['jenis_kelamin'];
        
    }
    
    public function fetchPassword(){
        $query = $this->db->prepare("select password from guru where nip = ?");
        $query->bindValue(1, $this->nip);
        try{
		
            $query->execute();
            $data = $query->fetch();
            $this->password = $data['password'];
            return $this->password;
 
	}catch(PDOException $e){
		die($e->getMessage());
	}
    }
    
    public function updatePassword($nip, $password){
        $query = $this->db->prepare("UPDATE guru "
                . "SET password=? "
                . "where nip=? ;");
        $query->bindValue(1, $password);
        $query->bindValue(2, $nip);
        try{
		
            $query->execute();
            $this->password = $password;
            return TRUE;
	}catch(PDOException $e){
            //die($e->getMessage());
            return false;
	}
    }
    
    public function userExists() {
        
        $strquery = "SELECT COUNT(`nip`) FROM `guru` WHERE `nip`= ?";
        $query = $this->db->prepare($strquery);
        $query->bindValue(1, $this->nip);

        try{

            $query->execute();
            $rows = $query->fetchColumn();

            if($rows == 1){
                return true;
            }else{
                return false;
            }

        } catch (PDOException $e){
            die($e->getMessage());
        }
    }
    
    public function add($nip, $password, $nama, $jenis_kelamin){
         $query = $this->db->prepare("insert into guru "
                . "SET nip=?, "
                . "password=?, "
                . "nama=?, "
                . "jenis_kelamin=?");
        $query->bindValue(1, $nip);
        $query->bindValue(2, $password);
        $query->bindValue(3, $nama);
        $query->bindValue(4, $jenis_kelamin);
        try{
		
            $query->execute();
            $this->nip = $nip;
            $this->password = $password;
            $this->nama = $nama;
            $this->jenis_kelamin = $jenis_kelamin;
            return TRUE;            
	}catch(PDOException $e){
            //die($e->getMessage());
            return FALSE;
	}
    }
    
    public function update($nip, $nama, $jenis_kelamin, $nip_lama){
        $query = $this->db->prepare("UPDATE guru "
                . "SET nip=?,  "
                . "nama=?, "
                . "jenis_kelamin=? "
                . "where nip=? ;");
        $query->bindValue(1, $nip);
        $query->bindValue(2, $nama);
        $query->bindValue(3, $jenis_kelamin);
        $query->bindValue(4, $nip_lama);
        try{
		
            $query->execute();
            $this->nip = $nip;
            $this->nama = $nama;
            $this->jenis_kelamin = $jenis_kelamin;
            return TRUE;
	}catch(PDOException $e){
            //die($e->getMessage());
            return false;
	}
    }

    public function delete($nip){
        $query = $this->db->prepare("delete from guru where nip = ? ");
        $query->bindValue(1, $nip);
        
        try{
		
            $query->execute();
            return true;
            
	}catch(PDOException $e){
            //die($e->getMessage());
            return false;
        }
    }
    
    public function upload($urlFIle){
        
        $pdo = $this->db;

        $config = new LexerConfig();
        $config->setDelimiter(";");
        $config->setIgnoreHeaderLine(TRUE);
        $lexer = new Lexer($config);

        $interpreter = new Interpreter();
        $interpreter->unstrict(); // Ignore row column count consistency

        $interpreter->addObserver(function(array $columns) use ($pdo) {
            $stmt = $pdo->prepare('REPLACE INTO guru (nip, nama, jenis_kelamin, password) '
                    . 'VALUES (?, ?, ?, ?)');
            $stmt->execute($columns);
        });
        try {
            $lexer->parse($urlFIle, $interpreter);
            return TRUE;
        } catch (Exception $ex) {
            return FALSE;
        }
    }
}