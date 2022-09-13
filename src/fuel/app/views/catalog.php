

        <table class = "table">
            <thead>
            <tr>
                <th>#</th>
                <th>Имя</th>
                <th>Телефон</th>
            </tr>
            </thead>

            <tbody>
            <?php
            foreach($catalog as $item) {
                ?>

                <tr>
                    <td><?php echo $item['id']; ?></td>
                    <td><?php echo $item['name']; ?></td>
                    <td><?php echo $item['phone']; ?></td>
                </tr>

                <?php
            }
            ?>
            </tbody>
        </table>

