<form id='add_fruit'>
	<table class="table">
		<thead>
			<tr>
				<th>Название</th>
				<th>Вес</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?foreach ($this->list_fruits as $key => $value) {
				$this->val = $value;
				$this->display("blocks/tr", false);	
			}?>
			<tr id="new_fruit">
				<td><input type="text" name="name" required></td>
				<td><input type="number" name="weight" required></td>
				<td><button date-type="add" class="js-fruits">+</button></td>
			</tr>
		</tbody>
	</table>
</form>