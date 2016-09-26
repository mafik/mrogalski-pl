# -*- coding: utf-8 -*-

S = []
N = 6

p = list(range(4))

def generuj():
	for a in range(N):
		for b in range(N):
			for c in range(N):
				for d in range(N):
					yield [a,b,c,d]

S = list(generuj())

def wspolne(a, b):
	suma = 0
	for ai, bi in zip(a, b):
		if ai == bi:
			suma += 1
	return suma

while len(S) > 1:
	najlepsza_opcja = [0] * 4
	ile_odrzuci_najlepsza = tuple([0] * N)
	for opcja in generuj():
		#print "Rozważam opcję ", opcja
		#do_odrzucenia = 10000000
		odrzucone = []
		for odpowiedz in range(4): # 0, 1, 2... 4
			do_wywalenia = [a for a in S if wspolne(a, opcja) == odpowiedz]
			#do_odrzucenia = min(do_odrzucenia, len(do_wywalenia))
			odrzucone.append(len(do_wywalenia))
			#print "  dla odopwiedzi ", odpowiedz, "wywalę", len(do_wywalenia)
		odrzucone.sort()
		odrzucone = tuple(odrzucone)
		if odrzucone >= ile_odrzuci_najlepsza:
			ile_odrzuci_najlepsza = odrzucone
			najlepsza_opcja = opcja

	odpowiedz = wspolne(najlepsza_opcja, p)
	S = [a for a in S if wspolne(a, najlepsza_opcja) == odpowiedz]

	print "Wybrałem opcję", najlepsza_opcja, 'pesymistycznie wyeliminuje ona', ile_odrzuci_najlepsza
	print "  odpowiedź kodera to", odpowiedz
	print "  pozostało", len(S), "możliwości"

print "Odpowiedź to", S[0]