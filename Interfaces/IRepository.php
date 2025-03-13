<?php
interface IRepository
{
    public function fetchAll($sort,$order);
    public function findById($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function checkEmailExist(string $email);
    // public function findByEmail(string $email);

}