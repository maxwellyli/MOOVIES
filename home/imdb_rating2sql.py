import mysql.connector
import gzip

def main():
  filename = raw_input('filename:   ').strip()
  limit = raw_input('limit:   ').strip()
  if is_num(limit):
    limit = int(limit)
  cnt = 0
  with gzip.open(filename) as file:
    cnx = mysql.connector.connect(user='root', password='cs411fa2016', database='imdb')
    cursor = cnx.cursor()
    for line in file:
      rating_tuple = parse_rating_tuple(line)
      if rating_tuple:
        insert_rating_tuple(rating_tuple, cursor)
        cnt += 1
        if cnt >= limit: break
    cnx.commit()
    cursor.close()
    cnx.close()
  print('Inserted %d ratings' % cnt)

def parse_rating_tuple(line):
  tokens = line.strip().split('  ')
  if len(tokens) == 4:
    # distribution, votes, rank, title
    d,v,r,t = tokens
    if is_num(d) and is_num(v) and is_num(r, parse_func=float):
      # remove the year from the end of the title
      t = t.strip()
      idx = t.index('(')
      yr = t[idx+1:idx+5]
      if not is_num(yr):
        print('Error parsing year from \'%s\' -> %s' % (t, yr))
      t = t[:idx]
      return r,t,yr
  return None

def is_num(s, parse_func=int):
  try:
    n = parse_func(s)
    return True
  except ValueError:
    return False

def insert_rating_tuple(rating_tuple, cursor):
  query = 'insert into rating(rating, title, year) values(%s, %s, %s)'
  # rating, title, year
  r,t,yr = rating_tuple
  cursor.execute(query, (r, t, yr))

main()

